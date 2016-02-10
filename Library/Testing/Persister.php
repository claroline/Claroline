<?php

namespace Claroline\CoreBundle\Library\Testing;

use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Resource\MaskDecoder;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;

/**
 * @service("claroline.library.testing.persister")
 */
class Persister {

    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var Role
     */
    private $userRole;

    /**
     * @InjectParams({
     *     "om" = @Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(ObjectManager $om) {
        $this->om = $om;
    }

    /**
     * @param string $username
     * @return User
     */
    public function user($username) {
        $user = new User();
        $user->setFirstName($username);
        $user->setLastName($username);
        $user->setUsername($username);
        $user->setPassword($username);
        $user->setMail($username . '@mail.com');
        $user->setGuid($username);
        $this->om->persist($user);

        if (!$this->userRole) {
            $this->userRole = $this->role('ROLE_USER');
            $this->om->persist($this->userRole);
        }

        $user->addRole($this->userRole);

        $workspace = new Workspace();
        $workspace->setName($username);
        $workspace->setCreator($user);
        $workspace->setCode($username);
        $workspace->setGuid($username);
        $this->om->persist($workspace);

        $user->setPersonalWorkspace($workspace);

        return $user;
    }

    /**
     * @param string $name
     * @return Role
     */
    public function role($name) {
        $role = new Role();
        $role->setName($name);
        $role->setTranslationKey($name);
        $this->om->persist($role);

        return $role;
    }

    public function maskDecoder(ResourceType $type, $permission, $value) {
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
        $organization->setEmail($name . '@gmail.com');
        $organization->setName($name);
        $this->om->persist($organization);

        return $organization;
    }

    /**
     * shortcut for persisting (if we don't want/need to add the object manager for our tests)
     */
    public function persist($entity)
    {
        $this->om->persist($entity);
    }

    /**
     * shortcut for flushing (if we don't want/need to add the object manager for our tests)
     */
    public function flush()
    {
        $this->om->flush();
    }
}