<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Security\Voter;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\AppBundle\Security\ObjectCollection;
use Claroline\AppBundle\Security\Voter\VoterInterface as ClarolineVoterInterface;
use Claroline\CoreBundle\Entity\Tool\AdminTool;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 *  This is the voter we use in the API. It's able to handle the ObjectCollection.
 */
abstract class AbstractVoter implements ClarolineVoterInterface, VoterInterface
{
    /** @var string */
    const CREATE = 'CREATE';
    /** @var string */
    const EDIT = 'EDIT';
    /** @var string */
    const ADMINISTRATE = 'ADMINISTRATE';
    /** @var string */
    const DELETE = 'DELETE';
    /** @var string */
    const VIEW = 'VIEW';
    /** @var string */
    const OPEN = 'OPEN';
    /** @var string */
    const PATCH = 'PATCH';

    /** @var ContainerInterface */
    protected $container;

    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param mixed $object
     *
     * @return int
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        //attributes[0] contains the permission (ie create, edit, open, ...)
        $attributes[0] = strtoupper($attributes[0]);

        if (!$this->supports($object) || !$this->supportsAction($attributes[0])) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        $collection = $this->getCollection($object);

        foreach ($collection as $object) {
            $access = $this->checkPermission($token, $object, $attributes, $collection->getOptions());
            if (VoterInterface::ACCESS_DENIED === $access) {
                return $access;
            }
        }

        //maybe abstain sometimes
        return VoterInterface::ACCESS_GRANTED;
    }

    /**
     * @param mixed $object
     *
     * @return ObjectCollection
     */
    protected function getCollection($object)
    {
        //here we can switch the old CollectionObjects for the new one so we can remove them laster
        //ie, remove GroupCollection, ResourceCollection, w/e

        if (!$object instanceof ObjectCollection) {
            $object = new ObjectCollection([$object]);
        }

        return $object;
    }

    /**
     * @return ContainerInterface
     */
    protected function getContainer()
    {
        return $this->container;
    }

    /**
     * @return ObjectManager
     */
    protected function getObjectManager()
    {
        return $this->getContainer()->get('Claroline\AppBundle\Persistence\ObjectManager');
    }

    /**
     * /!\ Try not to go infinite looping with this. Carreful.
     *
     * @param mixed $attributes
     * @param mixed $object
     *
     * @return bool
     */
    protected function isGranted($attributes, $object = null)
    {
        return $this->getContainer()->get('security.authorization_checker')->isGranted($attributes, $object);
    }

    /**
     * @param mixed $object
     *
     * @return bool
     */
    private function supports($object)
    {
        if ($object instanceof ObjectCollection) {
            return $object->isInstanceOf($this->getClass());
        } else {
            //doctrine sends proxy so we have to do the check with the instanceof operator
            $rc = new \ReflectionClass($this->getClass());
            $toCheck = $rc->newInstanceWithoutConstructor();

            return $object instanceof $toCheck;
        }
    }

    /**
     * @param string $action
     *
     * @return bool
     */
    private function supportsAction($action)
    {
        if (null === $this->getSupportedActions()) {
            return true;
        }

        return in_array($action, $this->getSupportedActions());
    }

    /**
     * @param string $attribute
     *
     * @return bool
     */
    public function supportsAttribute($attribute)
    {
        return true;
    }

    /**
     * @param string $class
     *
     * @return bool
     */
    public function supportsClass($class)
    {
        return true;
    }

    protected function isToolGranted($permission, string $toolName, Workspace $workspace = null)
    {
        $orderedToolRepo = $this->getObjectManager()->getRepository(OrderedTool::class);

        if ($workspace) {
            $orderedTool = $orderedToolRepo->findOneByNameAndWorkspace($toolName, $workspace);
        } else {
            $orderedTool = $orderedToolRepo->findOneByNameAndDesktop($toolName);
        }

        return $this->isGranted($permission, $orderedTool);
    }

    protected function hasAdminToolAccess(TokenInterface $token, string $name): bool
    {
        /** @var AdminTool $tool */
        $tool = $this->getObjectManager()
            ->getRepository(AdminTool::class)
            ->findOneBy(['name' => $name]);

        return $this->isGranted('OPEN', $tool);
    }

    /**
     * @param mixed $object
     *
     * @return bool
     */
    protected function isOrganizationManager(TokenInterface $token, $object)
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

    protected function isOrganizationMember(TokenInterface $token, $object)
    {
        if ($token->getUser() instanceof User) {
            $userOrganizations = $token->getUser()->getOrganizations();
            $objectOrganizations = $object->getOrganizations();

            foreach ($userOrganizations as $userOrganization) {
                foreach ($objectOrganizations as $objectOrganization) {
                    if ($objectOrganization === $userOrganization) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    protected function isAdmin(TokenInterface $token)
    {
        return $this->isGranted('ROLE_ADMIN');
    }

    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options)
    {
        $collection = isset($options['collection']) ? $options['collection'] : null;

        //crud actions
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

    public function getSupportedActions()
    {
        return [self::OPEN, self::VIEW, self::CREATE, self::EDIT, self::DELETE, self::PATCH];
    }
}
