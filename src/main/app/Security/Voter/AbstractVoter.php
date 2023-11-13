<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AppBundle\Security\Voter;

use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\AppBundle\Security\ObjectCollection;
use Claroline\AppBundle\Security\Voter\VoterInterface as ClarolineVoterInterface;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\CacheableVoterInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Security;

/**
 *  This is the voter we use in the API. It's able to handle the ObjectCollection.
 */
abstract class AbstractVoter implements ClarolineVoterInterface, CacheableVoterInterface
{
    private Security $security;
    private ObjectManager $om;

    public function setSecurity(Security $security): void
    {
        $this->security = $security;
    }

    public function setObjectManager(ObjectManager $om): void
    {
        $this->om = $om;
    }

    public function vote(TokenInterface $token, $subject, array $attributes): int
    {
        if (is_string($attributes[0])) {
            $attributes[0] = strtoupper($attributes[0]);
        }

        if (!$this->supports($subject) || !$this->supportsAttribute($attributes[0])) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        $collection = $this->getCollection($subject);
        foreach ($collection as $object) {
            $access = $this->checkPermission($token, $object, $attributes, $collection->getOptions());
            if (VoterInterface::ACCESS_DENIED === $access) {
                return $access;
            }
        }

        // maybe abstain sometimes
        return VoterInterface::ACCESS_GRANTED;
    }

    /**
     * @deprecated should not be injected in all Voters
     */
    protected function getObjectManager(): ObjectManager
    {
        return $this->om;
    }

    /**
     * /!\ Try not to go infinite looping with this. Careful.
     *
     * @deprecated do it yourself !
     */
    protected function isGranted($attributes, $object = null): bool
    {
        return $this->security->isGranted($attributes, $object);
    }

    private function supports($object): bool
    {
        return is_a($object, $this->getClass(), true)
            || ($object instanceof ObjectCollection && $object->isInstanceOf($this->getClass()));
    }

    public function supportsType(string $subjectType): bool
    {
        if (is_a($subjectType, ObjectCollection::class, true)) {
            return true;
        }

        return is_a($subjectType, $this->getClass(), true);
    }

    public function supportsAttribute(string $attribute): bool
    {
        if (null === $this->getSupportedActions()) {
            return true;
        }

        return in_array($attribute, $this->getSupportedActions());
    }

    /**
     * @deprecated use isContextToolGranted()
     */
    protected function isToolGranted($permission, string $toolName, Workspace $workspace = null): bool
    {
        return $this->isContextToolGranted($permission, !empty($workspace) ? 'workspace' : 'desktop', $workspace);
    }

    /**
     * @deprecated use isContextToolGranted()
     */
    protected function hasAdminToolAccess(TokenInterface $token, string $name): bool
    {
        return $this->isContextToolGranted('OPEN', $name, 'administration');
    }

    protected function isContextToolGranted(string $permission, string $toolName, string $context, ContextSubjectInterface|string $contextSubject = null): bool
    {
        $orderedToolRepo = $this->getObjectManager()->getRepository(OrderedTool::class);
        if ($contextSubject instanceof ContextSubjectInterface) {
            $contextId = $contextSubject->getContextIdentifier();
        } else {
            $contextId = $contextSubject;
        }

        $orderedTool = $orderedToolRepo->findOneByNameAndContext($toolName, $context, $contextId);

        return $this->isGranted($permission, $orderedTool);
    }

    /**
     * @deprecated use OrganizationManager::isManager()
     */
    protected function isOrganizationManager(TokenInterface $token, $object): bool
    {
        if (method_exists($object, 'getOrganizations') && $token->getUser() instanceof User) {
            $adminOrganizations = $token->getUser()->getAdministratedOrganizations();
            $objectOrganizations = $object->getOrganizations();

            foreach ($adminOrganizations as $adminOrganization) {
                foreach ($objectOrganizations as $objectOrganization) {
                    if ($objectOrganization === $adminOrganization) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options): int
    {
        $collection = isset($options['collection']) ? $options['collection'] : null;

        // crud actions
        switch ($attributes[0]) {
            case self::VIEW:         return $this->checkView($token, $object);
            case self::CREATE:       return $this->checkCreation($token, $object);
            case self::EDIT:         return $this->checkEdit($token, $object);
            case self::ADMINISTRATE: return $this->checkAdministrate($token, $object);
            case self::DELETE:       return $this->checkDelete($token, $object);
            case self::PATCH:        return $this->checkPatch($token, $object, $collection);
        }

        return VoterInterface::ACCESS_GRANTED;
    }

    private function checkView(TokenInterface $token, $object)
    {
        return VoterInterface::ACCESS_GRANTED;
    }

    private function checkCreation(TokenInterface $token, $object)
    {
        return VoterInterface::ACCESS_GRANTED;
    }

    private function checkEdit(TokenInterface $token, $object)
    {
        return VoterInterface::ACCESS_GRANTED;
    }

    private function checkAdministrate(TokenInterface $token, $object)
    {
        return VoterInterface::ACCESS_GRANTED;
    }

    private function checkDelete(TokenInterface $token, $object)
    {
        return VoterInterface::ACCESS_GRANTED;
    }

    private function checkPatch(TokenInterface $token, $object, ObjectCollection $collection = null)
    {
        return VoterInterface::ACCESS_GRANTED;
    }

    public function getSupportedActions(): ?array
    {
        return [self::OPEN, self::VIEW, self::CREATE, self::EDIT, self::DELETE, self::PATCH];
    }

    private function getCollection($object): ObjectCollection
    {
        if (!$object instanceof ObjectCollection) {
            $object = new ObjectCollection([$object]);
        }

        return $object;
    }
}
