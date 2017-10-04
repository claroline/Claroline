<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Security;

use Claroline\CoreBundle\Security\VoterInterface as ClarolineVoterInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

abstract class AbstractVoter implements ClarolineVoterInterface, VoterInterface
{
    const CREATE = 'CREATE';
    const EDIT = 'EDIT';
    const DELETE = 'DELETE';
    const VIEW = 'VIEW';
    const OPEN = 'OPEN';

    /**
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container")
     * })
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function vote(TokenInterface $token, $object, array $attributes)
    {
        //attributes[0] contains the permission (ie create, edit, open, ...)
        $attributes[0] = strtoupper($attributes[0]);

        if (!$this->supports($object) || !in_array($attributes[0], $this->getSupportedActions())) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        $collection = $this->getCollection($object);

        foreach ($collection as $object) {
            $access = $this->checkPermission($token, $object, $attributes, $collection->getOptions());
            if ($access === VoterInterface::ACCESS_DENIED) {
                return $access;
            }
        }

        //maybe abstain if sometimes
        return VoterInterface::ACCESS_GRANTED;
    }

    protected function getCollection($object)
    {
        //here we can switch the old CollectionObjects for the new one so we can remove them laster
        //ie, remove GroupCollection, ResourceCollection, w/e

        if (!$object instanceof ObjectCollection) {
            $object = new ObjectCollection([$object]);
        }

        return $object;
    }

    protected function getContainer()
    {
        return $this->container;
    }

    protected function getObjectManager()
    {
        return $this->getContainer()->get('claroline.persistence.object_manager');
    }

    private function supports($object)
    {
        return $object instanceof ObjectCollection ?
            $object->getClass() === $this->getClass() :
            get_class($object) === $this->getClass();
    }

    /**********************************************/
    /* WORTHLESS NOW BUT REQUIRED AND USED BY SF3 */
    /**********************************************/

    public function supportsAttribute($attribute)
    {
        return true;
    }

    public function supportsClass($class)
    {
        return true;
    }

    /***************************/
    /* COMMON UTILITIES METHOD */
    /***************************/

    protected function hasAdminToolAccess(TokenInterface $token, $name)
    {
        $tool = $this->getObjectManager()
          ->getRepository('ClarolineCoreBundle:Tool\AdminTool')
          ->findOneBy(['name' => $name]);

        $roles = $tool->getRoles();
        $tokenRoles = $token->getRoles();

        foreach ($tokenRoles as $tokenRole) {
            foreach ($roles as $role) {
                if ($role->getRole() === $tokenRole->getRole()) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function isOrganizationManager(TokenInterface $token, $object)
    {
        $adminOrganizations = $token->getUser()->getAdministratedOrganizations();
        $objectOrganizations = $object->getOrganizations();

        foreach ($adminOrganizations as $adminOrganization) {
            foreach ($objectOrganizations as $objectOrganization) {
                if ($objectOrganization === $adminOrganization) {
                    return true;
                }
            }
        }

        return false;
    }
}
