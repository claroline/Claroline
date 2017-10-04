<?php

namespace Claroline\CoreBundle\Library\Testing;

use Claroline\CoreBundle\Entity\Facet\Facet;
use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Claroline\CoreBundle\Entity\Facet\PanelFacet;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Organization\Location;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\ProfileProperty;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Entity\Resource\MaskDecoder;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use Symfony\Component\HttpFoundation\File\File as SfFile;

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
        $user->setPlainPassword($username);
        $user->setMail($username.'@mail.com');
        $user->addRole($roleUser);
        $user->setPublicUrl($username);
        $user->setCreationDate(new \DateTime());
        $this->container->get('claroline.manager.role_manager')->createUserRole($user);
        $this->om->persist($user);
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

    public function grantAdminRole(User $user)
    {
        $role = $this->role('ROLE_ADMIN');
        $user->addRole($role);
        $this->om->persist($user);
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

    public function facet($name, $forceCreationForm, $isMain)
    {
        return $this->container->get('claroline.manager.facet_manager')->createFacet($name, $forceCreationForm, $isMain);
    }

    public function panelFacet(Facet $facet, $name, $collapse, $autoEditable = false)
    {
        return $this->container->get('claroline.manager.facet_manager')->addPanel($facet, $name, $collapse, $autoEditable);
    }

    public function fieldFacet(PanelFacet $panelFacet, $name, $type, array $choices = [], $isRequired = false)
    {
        $this->om->startFlushSuite();
        $field = $this->container->get('claroline.manager.facet_manager')->addField($panelFacet, $name, $isRequired, $type);

        foreach ($choices as $choice) {
            $this->container->get('claroline.manager.facet_manager')->addFacetFieldChoice($choice, $field);
        }

        $this->om->endFlushSuite();

        return $field;
    }

    public function grantAdminToolAccess(User $user, $toolName)
    {
        $toolManager = $this->container->get('claroline.manager.tool_manager');
        $tool = $toolManager->getAdminToolByName($toolName);
        $role = $this->container->get('claroline.manager.role_manager')->getUserRole($user->getUsername());
        $toolManager->addRoleToAdminTool($tool, $role);
    }

    public function OauthClient($name, $grantTypes)
    {
        $client = $this->container->get('claroline.manager.oauth_manager')->createClient();
        $client->setName($name);
        $client->setAllowedGrantTypes($grantTypes);
        $this->om->persist($client);

        return $client;
    }

    public function profileProperty($property, $role, $isEditable = true)
    {
        $profileProperty = new ProfileProperty();
        $profileProperty->setProperty($property);
        $profileProperty->setIsEditable($isEditable);
        $profileProperty->setRole($this->role($role));
        $this->om->persist($profileProperty);

        return $profileProperty;
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
