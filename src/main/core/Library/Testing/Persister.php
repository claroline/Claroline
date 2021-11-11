<?php

namespace Claroline\CoreBundle\Library\Testing;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Location\Location;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Entity\Resource\MaskDecoder;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Symfony\Component\HttpFoundation\File\File as SfFile;

class Persister
{
    /**
     * @var ObjectManager
     */
    private $om;

    private $container;

    public function __construct(ObjectManager $om, $container)
    {
        $this->om = $om;
        $this->container = $container;
    }

    /**
     * @param string $username
     * @param bool   $personalWorkspace
     *
     * @return User
     */
    public function user($username, $personalWorkspace = false)
    {
        $roleUser = $this->om->getRepository('ClarolineCoreBundle:Role')->findOneByName('ROLE_USER');

        $user = new User();
        $user->setFirstName($username);
        $user->setLastName($username);
        $user->setUsername($username);
        $user->setPlainPassword($username);
        $user->setPassword($username);
        $user->setEmail($username.'@email.com');
        $user->setIsMailValidated(true);
        $user->addRole($roleUser);
        $user->setCreationDate(new \DateTime());
        $user->enable();
        $this->container->get('claroline.manager.role_manager')->createUserRole($user);
        $this->om->persist($user);

        // add a personal WS to the User
        if ($personalWorkspace) {
            $workspace = new Workspace();
            $workspace->setName($username);
            $workspace->setCreator($user);
            $workspace->setCode($username);
            $workspace->setUuid($username);

            $user->setPersonalWorkspace($workspace);
            $this->om->persist($workspace);
        }

        $this->om->flush();

        return $user;
    }

    /**
     * @param string $name
     *
     * @return Workspace
     */
    public function workspace($name, User $creator)
    {
        $workspace = new Workspace();
        $workspace->setName($name);
        $workspace->setCode($name);
        $workspace->setCreator($creator);
        $template = new SfFile($this->container->getParameter('claroline.param.default_template'));

        //optimize this later
        $this->container->get('claroline.manager.workspace_manager')->create($workspace, $template);

        return $workspace;
    }

    public function directory($name, ResourceNode $parent, Workspace $workspace, User $creator)
    {
        $directory = new Directory();
        $directory->setName($name);
        $dirType = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneByName('directory');

        return $this->container->get('claroline.manager.resource_manager')->create(
          $directory,
          $dirType,
          $creator,
          $workspace,
          $parent
      );
    }

    public function group($name)
    {
        $group = new Group();
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

    public function file($fileName, $mimeType, $withNode = false, User $creator = null)
    {
        $file = new File();
        $file->setSize(123);
        $file->setName($fileName);
        $file->setHashName(uniqid());
        $file->setMimeType($mimeType);
        $this->om->persist($file);

        if ($withNode && !$creator) {
            throw new \Exception('File requires a creator if you want to set a Resource Node.');
        }

        if ($withNode) {
            $fileType = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneByName('file');

            $this->container->get('claroline.manager.resource_manager')->create(
                $file,
                $fileType,
                $creator
            );
        }

        return $file;
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
