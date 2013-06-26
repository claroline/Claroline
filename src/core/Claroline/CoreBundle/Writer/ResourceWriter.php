<?php

namespace Claroline\CoreBundle\Writer;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Resource\ResourceIcon;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.writer.resource_writer")
 */
class ResourceWriter
{
    /** EntityManager */
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

    public function create(
        AbstractResource $resource,
        ResourceType $resourceType,
        User $creator,
        AbstractWorkspace $workspace,
        $name,
        ResourceIcon $icon,
        AbstractResource $parent = null,
        AbstractResource $previous = null,
        AbstractResource $next = null
    )
    {
        $resource->setCreator($creator);
        $resource->setWorkspace($workspace);
        $resource->setResourceType($resourceType);
        $resource->setParent($parent);
        $resource->setName($name);
        $resource->setPrevious($previous);
        $resource->setNext($next);
        $resource->setIcon($icon);
        $this->save($resource);

        return $resource;
    }

    public function setOrder(
        AbstractResource $resource,
        AbstractResource $previous = null,
        AbstractResource $next = null
    )
    {
        $resource->setPrevious($previous);
        $resource->setNext($next);
        $this->save($resource);

        return $resource;
    }

    public function move(
        AbstractResource $resource,
        AbstractResource $parent,
        $name
    )
    {
        $resource->setParent($parent);
        $resource->setName($name);
        $this->save($resource);
    }

    public function save(AbstractResource $resource)
    {
        $this->em->persist($resource);
        $this->em->flush();
    }

    public function remove(AbstractResource $resource)
    {
        $this->em->remove($resource);
        $this->em->flush();
    }
}