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
        AbstractResource $previous = null
    )
    {
        $resource->setCreator($creator);
        $resource->setWorkspace($workspace);
        $resource->setResourceType($resourceType);
        $resource->setParent($parent);
        $resource->setName($name);
        $resource->setPrevious($previous);
        $resource->setNext(null);
        $resource->setIcon($icon);
        $this->em->persist($resource);
        $this->em->flush();
        
        return $resource;
    }
}