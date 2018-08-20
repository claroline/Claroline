<?php

namespace Claroline\CoreBundle\Manager\Resource;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * ResourceRestrictionsManager.
 *
 * It validates access restrictions on ResourceNodes.
 *
 * @DI\Service("claroline.manager.resource_restrictions")
 */
class ResourceRestrictionsManager
{
    /** @var SessionInterface */
    private $session;

    /** @var RightsManager */
    private $rightsManager;

    /** @var AuthorizationCheckerInterface */
    private $security;

    /**
     * ResourceRestrictionsManager constructor.
     *
     * @DI\InjectParams({
     *     "session"       = @DI\Inject("session"),
     *     "rightsManager" = @DI\Inject("claroline.manager.rights_manager"),
     *     "security"      = @DI\Inject("security.authorization_checker")
     * })
     *
     * @param SessionInterface $session
     * @param RightsManager    $rightsManager
     */
    public function __construct(
        SessionInterface $session,
        RightsManager $rightsManager,
        AuthorizationCheckerInterface $security
    ) {
        $this->session = $session;
        $this->rightsManager = $rightsManager;
        $this->security = $security;
    }

    /**
     * Checks access restrictions of a ResourceNodes.
     *
     * @param ResourceNode $resourceNode
     * @param Role[]       $userRoles
     *
     * @return bool
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
                $errors['ended'] = $this->isEnded($resourceNode);
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
     * @param ResourceNode $resourceNode
     * @param Role[]       $userRoles
     *
     * @return bool
     */
    public function hasRights(ResourceNode $resourceNode, array $userRoles): bool
    {
        $isAdmin = false;

        if ($workspace = $resourceNode->getWorkspace()) {
            $isAdmin = $this->security->isGranted('administrate', $workspace);
        }

        return 0 !== $this->rightsManager->getMaximumRights($userRoles, $resourceNode) || $isAdmin;
    }

    /**
     * Checks if the access period of the resource is started.
     *
     * @param ResourceNode $resourceNode
     *
     * @return bool
     */
    public function isStarted(ResourceNode $resourceNode): bool
    {
        return empty($resourceNode->getAccessibleFrom()) || $resourceNode->getAccessibleFrom() <= new \DateTime();
    }

    /**
     * Checks if the access period of the resource is over.
     *
     * @param ResourceNode $resourceNode
     *
     * @return bool
     */
    public function isEnded(ResourceNode $resourceNode): bool
    {
        return empty($resourceNode->getAccessibleUntil()) || $resourceNode->getAccessibleUntil() > new \DateTime();
    }

    /**
     * Checks if the ip of the current user is allowed to access the resource.
     *
     * @param ResourceNode $resourceNode
     *
     * @return bool
     */
    public function isIpAuthorized(ResourceNode $resourceNode): bool
    {
        $allowed = $resourceNode->getAllowedIps();
        if (!empty($allowed)) {
            $currentParts = explode('.', $_SERVER['REMOTE_ADDR']);

            foreach ($allowed as $allowedIp) {
                $allowedParts = explode('.', $allowedIp);
                $allowBlock = [];

                foreach ($allowedParts as $key => $val) {
                    $allowBlock[] = ($val === $currentParts[$key] || '*' === $val);
                }

                if (!in_array(false, $allowBlock)) {
                    return true;
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
     *
     * @param ResourceNode $resourceNode
     *
     * @return bool
     */
    public function isUnlocked(ResourceNode $resourceNode): bool
    {
        if ($resourceNode->getAccessCode()) {
            // check if the current user already has unlocked the resource
            // maybe store it another way to avoid require it each time the user session expires
            return !empty($this->session->get($resourceNode->getUuid()));
        }

        // the current resource does not require a code
        return true;
    }

    /**
     * Submits a code to unlock a resource.
     * NB. The resource will stay unlocked as long as the user session stay alive.
     *
     * @param ResourceNode $resourceNode - The resource to unlock
     * @param string       $code         - The code sent by the user
     *
     * @throws InvalidDataException - If the submitted code is incorrect
     */
    public function unlock(ResourceNode $resourceNode, $code = null)
    {
        //if a code is defined
        if ($accessCode = $resourceNode->getAccessCode()) {
            if (empty($code) || $accessCode !== $code) {
                $this->session->set($resourceNode->getUuid(), false);

                throw new InvalidDataException('Invalid code sent');
            }

            $this->session->set($resourceNode->getUuid(), true);
        }
    }
}
