<?php

namespace Claroline\CoreBundle\Writer;

use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\ResourceRights;
use Claroline\CoreBundle\Entity\Role;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.writer.rights_writer")
 */
class RightsWriter
{
    /** @var EntityManager */
    private $em;

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager")
     * })
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function createFrom(AbstractResource $resource, Role $role, ResourceRights $from)
    {
        $rights = new ResourceRights();
        $rights->setResource($resource);
        $rights->setRole($role);
        $rights->setRightsFrom($from);

        if ($resource->getResourceType()->getName() === 'directory') {
            $rights->setCreatableResourceTypes($from->getCreatableResourceTypes()->toArray());
        }

        $this->em->persist($resource);
        $this->em->flush();

        return $rights;
    }

    public function create(array $permissions, array $creations, AbstractResource $resource, Role $role)
    {
        $rights = new ResourceRights();
        $this->setPermissions($rights, $permissions);
        $rights->setCreatableResourceTypes($creations);
        $rights->setRole($role);
        $rights->setResource($resource);
        $this->em->persist($rights);
        $this->em->flush();

        return $rights;
    }

    public function edit(ResourceRights $rights, array $permissions, array $creations = array())
    {
        $rights->setPermissions($rights, $permissions);
        $rights->setCreatableResourceTypes($creations);
        $this->em->persist($rights);
        $this->em->flush();

        return $rights;
    }

    private function setPermissions(ResourceRights $rights, array $permissions)
    {
        $rights->setCanCopy($permissions['canCopy']);
        $rights->setCanOpen($permissions['canOpen']);
        $rights->setCanDelete($permissions['canDelete']);
        $rights->setCanEdit($permissions['canEdit']);
        $rights->setCanExport($permissions['canExport']);

        return $rights;
    }
}
