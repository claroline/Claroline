<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CommunityBundle\Security\Voter;

use Claroline\AppBundle\Security\ObjectCollection;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class RoleVoter extends AbstractVoter
{
    public function getClass()
    {
        return Role::class;
    }

    /**
     * @param Role $object
     */
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options)
    {
        $collection = isset($options['collection']) ? $options['collection'] : null;

        switch ($attributes[0]) {
            case self::OPEN:
                if (in_array($object->getName(), $token->getRoleNames())) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                return VoterInterface::ACCESS_DENIED;
            case self::EDIT:
                return $this->check($token, $object);
            case self::PATCH:
                return $this->checkPatch($token, $object, $collection);
            case self::DELETE:
                return $this->checkDelete($token, $object);
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }

    protected function checkPatch(TokenInterface $token, Role $role, $collection): int
    {
        if ($collection->isInstanceOf(User::class) || $collection->isInstanceOf(Group::class)) {
            $grant = true;
            foreach ($collection as $object) {
                $grant = $grant && $this->isGranted(self::PATCH, new ObjectCollection([$object], ['collection' => new ObjectCollection([$role])]));
                if (!$grant) {
                    // no need to continue
                    break;
                }
            }

            if ($grant) {
                return VoterInterface::ACCESS_GRANTED;
            }

            return VoterInterface::ACCESS_DENIED;
        }

        return $this->check($token, $role);
    }

    protected function check(TokenInterface $token, Role $object): int
    {
        // probably do the check from the UserVoter or a security issue will arise
        if (!$object->getWorkspace()) {
            return $this->hasAdminToolAccess($token, 'community') ?
              VoterInterface::ACCESS_GRANTED : VoterInterface::ACCESS_DENIED;
        }

        // if it's a workspace role, we must be granted the edit perm on the workspace users tool
        // and our right level to be less than the role we're trying to remove that way, a user cannot remove admins
        $workspace = $object->getWorkspace();
        if ($this->isGranted(['community', 'edit'], $workspace)) {
            $workspaceManager = $this->getContainer()->get('claroline.manager.workspace_manager');
            // If user is workspace manager then grant access
            if ($workspaceManager->isManager($object->getWorkspace(), $token)) {
                return VoterInterface::ACCESS_GRANTED;
            }

            // Otherwise only allow modification of roles the current user owns
            if (in_array($object->getName(), $token->getRoleNames())) {
                return VoterInterface::ACCESS_GRANTED;
            }
        }

        // If public registration is enabled and user try to get the default role, grant access
        if ($workspace->getSelfRegistration() && $workspace->getDefaultRole()) {
            if ($workspace->getDefaultRole()->getId() === $object->getId()) {
                return VoterInterface::ACCESS_GRANTED;
            }
        }

        return VoterInterface::ACCESS_DENIED;
    }

    protected function checkDelete(TokenInterface $token, Role $object): int
    {
        if ($object->isReadOnly()) {
            return VoterInterface::ACCESS_DENIED;
        }

        return $this->check($token, $object);
    }

    public function getSupportedActions()
    {
        return [self::CREATE, self::OPEN, self::EDIT, self::DELETE, self::PATCH];
    }
}
