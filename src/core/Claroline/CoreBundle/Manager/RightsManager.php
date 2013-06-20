<?php

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Writer\RightsWriter;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Repository\ResourceRightsRepository;
use Claroline\CoreBundle\Repository\AbstractResourceRepository;

/**
 * @DI\Service("claroline.manager.rights_manager")
 */
class RightsManager
{
    /** @var RightsWriter */
    private $writer;
    /** @var ResourceRightsRepository */
    private $rightsRepo;
    /** @var AbstractResourceRepository */
    private $resourceRepo;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "writer" = @DI\Inject("claroline.writer.rights_writer"),
     *     "rightsRepo" = @Di\Inject("resource_rights_repository"),
     *     "resourceRepo" = @Di\Inject("resource_rights_repository")
     * })
     */
    public function __construct(
        RightsWriter $writer,
        ResourceRightsRepository $rightsRepo,
        AbstractResourceRepository $resourceRepo
    )
    {
        $this->writer = $writer;
        $this->rightsRepo = $rightsRepo;
        $this->resourceRepo = $resourceRepo;
    }

    /**
     * Create a new ResourceRight
     *
     * @param array $permissions
     * @param boolean $isRecursive
     * @param \Claroline\CoreBundle\Entity\Role $role
     * @param \Claroline\CoreBundle\Entity\Resource\AbstractResource $resource
     */
    public function create(
        array $permissions,
        Role $role,
        AbstractResource $resource,
        $isRecursive,
        array $creations = array()
    )
    {
        $resourceRights = array();

        if ($isRecursive) {
            $resourceRights = $this->addMissing($role, $resource);
        } else {
            $resourceRights[] = $this->writer->create($this->getFalsePermissions(), array(), $resource, $role);
        }

        foreach ($resourceRights as $resourceRight) {
            $this->writer->edit($resourceRight, $permissions, $creations);
        }
    }

    public function editRights(AbstractResource $resource, Role $role, array $permissions, array $creations = array())
    {
        $rights = $this->rightsRepo->findOneBy(array('resource' => $resource, 'role' => $role));
        $this->writer->edit($rights, $permissions, $creations);

        return $rights;
    }

    public function cloneRights(AbstractResource $resource)
    {
       $resourceRights = $this->repo->findBy(array('resource' => $resource));

       foreach ($resourceRights as $resourceRight) {
           $created[] = $this->writer->createFrom($resource, $resourceRight->getRole(), $resourceRight);
       }

       return $created;
    }

    /**
     * Sets the resource rights of a resource.
     * Expects an array of role of the following form:
     * array('ROLE_WS_MANAGER' => array('canOpen' => true, 'canEdit' => false', ...)
     * The 'canCopy' key must contain an array of resourceTypes name.
     */
    public function setResourceRightsFromArray(AbstractResource $resource, array $rights, array $roles = array())
    {
        $roleRepo = $this->em->getRepository('ClarolineCoreBundle:Role');
        $resourceTypeRepo = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceType');
        $workspace = $resource->getWorkspace();

        foreach ($rights as $role => $permissions) {
            $resourceTypes = array();
            $unknownTypes = array();

            foreach ($permissions['canCreate'] as $type) {
                $rt = $resourceTypeRepo->findOneByName($type);
                if ($rt === null) {
                    $unknownTypes[] = $type['name'];
                }
                $resourceTypes[] = $rt;
            }

            if (count($unknownTypes) > 0) {
                $content = "The resource type(s) ";
                foreach ($unknownTypes as $unknown) {
                    $content .= "{$unknown}, ";
                }
                $content .= "were not found";

                throw new \Exception($content);
            }

            if (count($roles) === 0) {
                $role = $roleRepo->findOneBy(array('name' => $role.'_'.$workspace->getId()));
            } else {
                $role = $roles[$role.'_'.$workspace->getId()];
            }
            $this->createRight($permissions, false, $role, $resource, $resourceTypes, false);
        }

        $this->createRight(
            $this->getFalsePermissions(),
            $roleRepo->findOneBy(array('name' => 'ROLE_ANONYMOUS')),
            $resource,
            array(),
            false
        );

        $resourceTypes = $resourceTypeRepo->findAll();

        $this->createRight(
            $this->getTruePermissions(),
            $roleRepo->findOneBy(array('name' => 'ROLE_ADMIN')),
            $resource,
            $resourceTypes,
            false
        );
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Role $role
     * @param \Claroline\CoreBundle\Entity\Resource\AbstractResource $resource
     *
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceRights
     */
    public function addMissing(Role $role, AbstractResource $resource)
    {
        $alreadyExistings = $this->rightsRepo->findRecursiveByResourceAndRole($resource, $role);
        $descendants = $this->resourceRepo->findDescendants($resource, true);
        $finalRights = array();

        foreach ($descendants as $descendant) {
            $found = false;
            foreach ($alreadyExistings as $existingRight) {
                if ($existingRight->getResource() === $descendant) {
                    $finalRights[] = $existingRight;
                    $found = true;
                }
            }

            if (!$found) {
                $finalRights[] = $this->writer->create($this->getFalsePermissions(), array(), $resource, $role);
            }
        }

        return $finalRights;
    }

    public function getFalsePermissions()
    {
        return array(
            'canCopy' => false,
            'canOpen' => false,
            'canDelete' => false,
            'canEdit' => false,
            'canExport' => false
        );
    }

    public function getTruePermissions()
    {
        return array(
            'canCopy' => true,
            'canOpen' => true,
            'canDelete' => true,
            'canEdit' => true,
            'canExport' => true
        );
    }
}