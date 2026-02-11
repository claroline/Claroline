<?php

namespace Claroline\CoreBundle\Manager\Resource;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * ResourceRestrictionsManager.
 *
 * It validates access restrictions on ResourceNodes.
 */
class ResourceRestrictionsManager
{
    private const NO_RIGHTS = 'NO_RIGHTS';
    private const NOT_PUBLISHED = 'NOT_PUBLISHED';
    private const INVALID_DATES = 'INVALID_DATES';
    private const ACCESS_CODE = 'ACCESS_CODE';

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly RightsManager $rightsManager,
        private readonly AuthorizationCheckerInterface $authorization
    ) {
    }

    /**
     * Checks access restrictions of a resource.
     *
     * @param string[] $userRoles
     */
    public function isGranted(ResourceNode $resourceNode, array $userRoles): bool
    {
        return $this->hasRights($resourceNode, $userRoles)
            && $resourceNode->isActive()
            && $resourceNode->isPublished()
            && ($this->isStarted($resourceNode) && !$this->isEnded($resourceNode))
            && $this->isUnlocked($resourceNode);
    }

    public function getError(ResourceNode $resourceNode, array $userRoles): ?array
    {
        if (!$this->hasRights($resourceNode, $userRoles)) {
            return [
                'code' => self::NO_RIGHTS,
                'message' => 'You don\'t have the permissions to access this resource.',
            ];
        }

        if (!$resourceNode->isActive() || !$resourceNode->isPublished()) {
            return [
                'code' => self::NOT_PUBLISHED,
                'message' => !$resourceNode->isPublished() ?
                    'The resource is not published.' :
                    'The resource is archived.',
                'additional' => [
                    'archived' => !$resourceNode->isActive(),
                ],
            ];
        }

        if (!$this->isStarted($resourceNode) || $this->isEnded($resourceNode)) {
            return [
                'code' => self::INVALID_DATES,
                'message' => !$this->isStarted($resourceNode) ?
                    'The access period of the resource is not started yet.' :
                    'The access period of the resource is ended.',
                'additional' => [
                    'startDate' => DateNormalizer::normalize($resourceNode->getAccessibleFrom()),
                    'endDate' => DateNormalizer::normalize($resourceNode->getAccessibleUntil()),
                ],
            ];
        }

        if (!$this->isUnlocked($resourceNode)) {
            return [
                'code' => self::ACCESS_CODE,
                'message' => 'This resource requires an access code to be opened.',
            ];
        }

        return null;
    }

    /**
     * Checks if a user has at least the right to access to one of the resource actions.
     *
     * @param string[] $userRoles
     */
    private function hasRights(ResourceNode $resourceNode, array $userRoles): bool
    {
        if ($resourceNode->isPublic()) {
            return true;
        }

        $isAdmin = false;

        $workspace = $resourceNode->getWorkspace();
        if ($workspace) {
            $isAdmin = $this->authorization->isGranted('administrate', $workspace);
        }

        return $isAdmin || 0 !== $this->rightsManager->getMaximumRights($userRoles, $resourceNode);
    }

    /**
     * Checks if the access period of the resource is started.
     */
    public function isStarted(ResourceNode $resourceNode): bool
    {
        return empty($resourceNode->getAccessibleFrom()) || $resourceNode->getAccessibleFrom() <= new \DateTime();
    }

    /**
     * Checks if the access period of the resource is over.
     */
    public function isEnded(ResourceNode $resourceNode): bool
    {
        return !empty($resourceNode->getAccessibleUntil()) && $resourceNode->getAccessibleUntil() <= new \DateTime();
    }

    /**
     * Checks if a resource is unlocked.
     * (aka it has no access code, or user has already submitted it).
     */
    public function isUnlocked(ResourceNode $resourceNode): bool
    {
        if ($resourceNode->getAccessCode()) {
            $currentRequest = $this->requestStack->getCurrentRequest();

            // check if the current user already has unlocked the resource
            // maybe store it another way to avoid require it each time the user session expires
            return !empty($currentRequest->getSession()->get($resourceNode->getUuid()));
        }

        // the current resource does not require a code
        return true;
    }

    /**
     * Submits a code to unlock a resource.
     * NB. The resource will stay unlocked as long as the user session stays alive.
     */
    public function unlock(ResourceNode $resourceNode, string $code = null): void
    {
        $accessCode = $resourceNode->getAccessCode();
        if ($accessCode) {
            $currentRequest = $this->requestStack->getCurrentRequest();

            if (empty($code) || $accessCode !== $code) {
                $currentRequest->getSession()->set($resourceNode->getUuid(), false);

                throw new InvalidDataException('Invalid code sent');
            }

            $currentRequest->getSession()->set($resourceNode->getUuid(), true);
        }
    }
}
