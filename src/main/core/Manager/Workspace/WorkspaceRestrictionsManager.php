<?php

namespace Claroline\CoreBundle\Manager\Workspace;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * WorkspaceRestrictionsManager.
 *
 * It validates access restrictions on Workspaces.
 *
 * @todo merge restrictions checks with ResourceRestrictionsManager.
 */
class WorkspaceRestrictionsManager
{
    /** @var RequestStack */
    private $requestStack;
    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var WorkspaceManager */
    private $workspaceManager;
    /** @var WorkspaceUserQueueManager */
    private $workspaceUserQueueManager;

    public function __construct(
        RequestStack $requestStack,
        AuthorizationCheckerInterface $authorization,
        WorkspaceManager $workspaceManager,
        WorkspaceUserQueueManager $workspaceUserQueueManager
    ) {
        $this->requestStack = $requestStack;
        $this->authorization = $authorization;
        $this->workspaceManager = $workspaceManager;
        $this->workspaceUserQueueManager = $workspaceUserQueueManager;
    }

    /**
     * Checks access restrictions of a workspace.
     */
    public function isGranted(Workspace $workspace): bool
    {
        return $this->hasRights($workspace)
            && !$workspace->isArchived()
            && ($this->isStarted($workspace) && !$this->isEnded($workspace))
            && $this->isUnlocked($workspace)
            && $this->isIpAuthorized($workspace);
    }

    /**
     * Gets the list of access error for a workspace and a user.
     */
    public function getErrors(Workspace $workspace, User $user = null): array
    {
        if (!$this->isGranted($workspace)) {
            // return restrictions details
            $errors = [
                'noRights' => !$this->hasRights($workspace),
                'selfRegistration' => $workspace->getSelfRegistration(),
                'archived' => $workspace->isArchived(),
            ];

            if ($user) {
                $errors['registered'] = $this->workspaceManager->isRegistered($workspace, $user);
                $errors['pendingRegistration'] = $this->workspaceUserQueueManager->isUserInValidationQueue($workspace, $user);
            }

            // optional restrictions
            // we return them only if they are enabled
            if (!empty($workspace->getAccessCode())) {
                $errors['locked'] = !$this->isUnlocked($workspace);
            }

            if (!empty($workspace->getAccessibleFrom()) || !empty($workspace->getAccessibleUntil())) {
                $errors['notStarted'] = !$this->isStarted($workspace);
                $errors['startDate'] = DateNormalizer::normalize($workspace->getAccessibleFrom());
                $errors['ended'] = $this->isEnded($workspace);
                $errors['endDate'] = DateNormalizer::normalize($workspace->getAccessibleUntil());
            }

            if (!empty($workspace->getAllowedIps())) {
                $errors['invalidLocation'] = !$this->isIpAuthorized($workspace);
            }

            return $errors;
        }

        return [];
    }

    /**
     * Checks if a user has at least the right to access the workspace.
     */
    public function hasRights(Workspace $workspace): bool
    {
        return $this->authorization->isGranted('open', $workspace);
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
     * Checks if the ip of the current user is allowed to access the workspace.
     *
     * @todo works just with IPv4, should be working with IPv6
     */
    public function isIpAuthorized(Workspace $workspace): bool
    {
        $allowed = $workspace->getAllowedIps();
        if (!empty($allowed)) {
            $currentRequest = $this->requestStack->getCurrentRequest();
            if ($currentRequest && $currentRequest->getClientIp()) {
                $currentParts = explode('.', $currentRequest->getClientIp());

                foreach ($allowed as $allowedIp) {
                    $allowedParts = explode('.', $allowedIp);
                    $allowBlock = [];

                    foreach ($allowedParts as $key => $val) {
                        if (isset($currentParts[$key])) {
                            $allowBlock[] = ($val === $currentParts[$key] || '*' === $val);
                        }
                    }

                    if (!in_array(false, $allowBlock)) {
                        return true;
                    }
                }
            }

            // the current user ip is not in the allowed list
            return false;
        }

        // the current workspace does not restrict ips
        return true;
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
     * NB. The workspace will stay unlocked as long as the user session stay alive.
     *
     * @param Workspace $workspace - The workspace to unlock
     * @param string    $code      - The code sent by the user
     *
     * @throws InvalidDataException - If the submitted code is incorrect
     */
    public function unlock(Workspace $workspace, string $code = null)
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
