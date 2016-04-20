<?php

namespace Claroline\CoreBundle\Library\Testing;

use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\Organization\Location;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Resource\MaskDecoder;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;

/**
 * @service("claroline.library.testing.persister")
 */
class Persister
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var Role
     */
    private $userRole;

    private $container;

    /**
     * @InjectParams({
     *     "om"        = @Inject("claroline.persistence.object_manager"),
     *     "container" = @Inject("service_container")
     * })
     */
    public function __construct(ObjectManager $om, $container)
    {
        $this->om = $om;
        $this->container = $container;
    }

    /**
     * @param string $username
     *
     * @return User
     */
    public function user($username)
    {
        $roleUser = $this->om->getRepository('ClarolineCoreBundle:Role')->findOneByName('ROLE_USER');

        $user = new User();
        $user->setFirstName($username);
        $user->setLastName($username);
        $user->setUsername($username);
        $user->setPassword($username);
        $user->setMail($username.'@mail.com');
        $user->setGuid(uniqid());
        $user->addRole($roleUser);
        $this->container->get('claroline.manager.role_manager')->createUserRole($user);
        $this->om->persist($user);

        return $user;
    }

    public function group($name)
    {
        $group = new Group();
        $group->setGuid($this->container->get('claroline.utilities.misc')->generateGuid());
        $group->setName($name);
        $this->om->persist($group);

        return $group;
    }

    /**
     * @param string $name
     *
     * @return Role
     */
    public function role($name)
    {
        $role = $this->om->getRepository('ClarolineCoreBundle:Role')->findOneByName($name);

        if (!$role) {
            $role = new Role();
            $role->setName($name);
            $role->setTranslationKey($name);
            $this->om->persist($role);
        }

        return $role;
    }

    public function maskDecoder(ResourceType $type, $permission, $value)
    {
        $decoder = new MaskDecoder();
        $decoder->setResourceType($type);
        $decoder->setName($permission);
        $decoder->setValue($value);
        $this->om->persist($decoder);

        return $decoder;
    }

    public function organization($name)
    {
        $organization = new Organization();
        $organization->setEmail($name.'@gmail.com');
        $organization->setName($name);
        $this->om->persist($organization);

        return $organization;
    }

    public function location($name)
    {
        $location = new Location();
        $location->setName($name);
        $location->setStreet($name);
        $location->setStreetNumber($name);
        $location->setBoxNumber($name);
        $location->setPc($name);
        $location->setTown($name);
        $location->setCountry($name);
        $location->setLatitude(123);
        $location->setLongitude(123);
        $this->om->persist($location);

        return $location;
    }

    /**
     * shortcut for persisting (if we don't want/need to add the object manager for our tests).
     */
    public function persist($entity)
    {
        $this->om->persist($entity);
    }

    /**
     * shortcut for flushing (if we don't want/need to add the object manager for our tests).
     */
    public function flush()
    {
        $this->om->flush();
    }
}
