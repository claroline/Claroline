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
    /** @var RequestStack */
    private $requestStack;

    /** @var RightsManager */
    private $rightsManager;

    /** @var AuthorizationCheckerInterface */
    private $authorization;

    public function __construct(
        RequestStack $requestStack,
        RightsManager $rightsManager,
        AuthorizationCheckerInterface $authorization
    ) {
        $this->requestStack = $requestStack;
        $this->rightsManager = $rightsManager;
        $this->authorization = $authorization;
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
            && $this->isUnlocked($resourceNode)
            && $this->isIpAuthorized($resourceNode);
    }

    /**
     * Gets the list of access error for a resource and a user roles.
     *
     * @param string[] $userRoles
     */
    public function getErrors(ResourceNode $resourceNode, array $userRoles): array
    {
        if (!$this->isGranted($resourceNode, $userRoles)) {
            // return restrictions details
            $errors = [
                'noRights' => !$this->hasRights($resourceNode, $userRoles),
                'deleted' => !$resourceNode->isActive(),
                'notPublished' => !$resourceNode->isPublished(),
            ];

            // optional restrictions
            // we return them only if they are enabled
            if (!empty($resourceNode->getAccessCode())) {
                $errors['locked'] = !$this->isUnlocked($resourceNode);
            }

            if (!empty($resourceNode->getAccessibleFrom()) || !empty($resourceNode->getAccessibleUntil())) {
                $errors['notStarted'] = !$this->isStarted($resourceNode);
                $errors['startDate'] = DateNormalizer::normalize($resourceNode->getAccessibleFrom());
                $errors['ended'] = $this->isEnded($resourceNode);
                $errors['endDate'] = DateNormalizer::normalize($resourceNode->getAccessibleUntil());
            }

            if (!empty($resourceNode->getAllowedIps())) {
                $errors['invalidLocation'] = !$this->isIpAuthorized($resourceNode);
            }

            return $errors;
        }

        return [];
    }

    /**
     * Checks if a user has at least the right to access to one of the resource action.
     *
     * @param string[] $userRoles
     */
    public function hasRights(ResourceNode $resourceNode, array $userRoles): bool
    {
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
     * Checks if the ip of the current user is allowed to access the resource.
     *
     * @todo works just with IPv4, should be working with IPv6
     */
    public function isIpAuthorized(ResourceNode $resourceNode): bool
    {
        $allowed = $resourceNode->getAllowedIps();
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

        // the current resource does not restrict ips
        return true;
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
     * NB. The resource will stay unlocked as long as the user session stay alive.
     */
    public function unlock(ResourceNode $resourceNode, string $code = null)
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
