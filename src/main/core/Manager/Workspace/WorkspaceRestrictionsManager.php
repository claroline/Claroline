<?php

namespace Claroline\CoreBundle\Manager\Workspace;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\CatalogEvents\WorkspaceEvents;
use Claroline\CoreBundle\Event\Workspace\AccessRestrictedWorkspaceEvent;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * WorkspaceRestrictionsManager.
 *
 * It validates access restrictions on Workspaces.
 *
 * @todo merge restrictions checks with ResourceRestrictionsManager.
 */
class WorkspaceRestrictionsManager
{
    private const NO_RIGHTS = 'NO_RIGHTS';
    private const NOT_PUBLISHED = 'NOT_PUBLISHED';
    private const NOT_REGISTERED = 'NOT_REGISTERED';
    private const INVALID_DATES = 'INVALID_DATES';
    private const ACCESS_CODE = 'ACCESS_CODE';

    public function __construct(
        private readonly EventDispatcherInterface $dispatcher,
        private readonly RequestStack $requestStack,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly WorkspaceManager $workspaceManager,
        private readonly WorkspaceUserQueueManager $workspaceUserQueueManager
    ) {
    }

    /**
     * Checks access restrictions of a workspace.
     */
    public function isGranted(Workspace $workspace): bool
    {
        return $this->hasRights($workspace)
            && !$workspace->isArchived()
            && ($this->isStarted($workspace) && !$this->isEnded($workspace))
            && $this->isUnlocked($workspace);
    }

    public function getError(Workspace $workspace, ?User $user = null): ?array
    {
        if (!$this->hasRights($workspace)) {
            $event = new AccessRestrictedWorkspaceEvent($workspace);
            $this->dispatcher->dispatch($event, WorkspaceEvents::ACCESS_RESTRICTED);

            if ($event->getError()) {
                return $event->getError();
            }

            if (!$user || !$this->workspaceManager->isRegistered($workspace, $user)) {
                return [
                    'code' => self::NOT_REGISTERED,
                    'additional' => [
                        'selfRegistration' => !$workspace->isArchived() && $workspace->getSelfRegistration(),
                        'pendingRegistration' => $user ? $this->workspaceUserQueueManager->isUserInValidationQueue($workspace, $user) : null,
                    ],
                ];
            }

            return [
                'code' => self::NO_RIGHTS,
                'message' => 'You don\'t have the permissions to access this workspace.',
            ];
        }

        if ($workspace->isArchived()) {
            return [
                'code' => self::NOT_PUBLISHED,
                'message' => 'The workspace is archived.',
                'additional' => [
                    'archived' => true,
                ],
            ];
        }

        if (!$this->isStarted($workspace) || $this->isEnded($workspace)) {
            return [
                'code' => self::INVALID_DATES,
                'message' => !$this->isStarted($workspace) ?
                    'The access period of the workspace is not started yet.' :
                    'The access period of the workspace is ended.',
                'additional' => [
                    'startDate' => DateNormalizer::normalize($workspace->getAccessibleFrom()),
                    'endDate' => DateNormalizer::normalize($workspace->getAccessibleUntil()),
                ],
            ];
        }

        if (!$this->isUnlocked($workspace)) {
            return [
                'code' => self::ACCESS_CODE,
                'message' => 'This resource requires an access code to be opened.',
            ];
        }

        return null;
    }

    /**
     * Checks if a user has at least the right to access one workspace tool.
     */
    private function hasRights(Workspace $workspace): bool
    {
        // we don't simply call the auth checker because it will check all the restrictions (e.g., dates, code)
        // not only the user rights
        return $this->workspaceManager->hasAccess($workspace, $this->tokenStorage->getToken());
    }

    /**
     * Checks if the access period of the workspace is started.
     */
    public function isStarted(Workspace $workspace): bool
    {
        return empty($workspace->getAccessibleFrom()) || $workspace->getAccessibleFrom() <= new \DateTime();
    }

    /**
     * Checks if the access period of the workspace is over.
     */
    public function isEnded(Workspace $workspace): bool
    {
        return !empty($workspace->getAccessibleUntil()) && $workspace->getAccessibleUntil() <= new \DateTime();
    }

    /**
     * Checks if a workspace is unlocked.
     * (aka it has no access code, or user has already submitted it).
     */
    public function isUnlocked(Workspace $workspace): bool
    {
        if ($workspace->getAccessCode()) {
            $currentRequest = $this->requestStack->getCurrentRequest();

            // check if the current user already has unlocked the workspace
            // maybe store it another way to avoid require it each time the user session expires
            return !empty($currentRequest->getSession()->get($workspace->getUuid()));
        }

        // the current workspace does not require a code
        return true;
    }

    /**
     * Submits a code to unlock a workspace.
     * NB. The workspace will stay unlocked as long as the user session stays alive.
     *
     * @param Workspace $workspace - The workspace to unlock
     * @param string    $code      - The code sent by the user
     *
     * @throws InvalidDataException - If the submitted code is incorrect
     */
    public function unlock(Workspace $workspace, string $code = null): void
    {
        $accessCode = $workspace->getAccessCode();
        if ($accessCode) {
            $currentRequest = $this->requestStack->getCurrentRequest();

            if (empty($code) || $accessCode !== $code) {
                $currentRequest->getSession()->set($workspace->getUuid(), false);

                throw new InvalidDataException('Invalid code sent');
            }

            $currentRequest->getSession()->set($workspace->getUuid(), true);
        }
    }
}
