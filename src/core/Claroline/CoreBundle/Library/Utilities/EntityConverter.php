<?php

namespace Claroline\CoreBundle\Library\Utilities;

use \stdClass;
use \RuntimeException;
use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Entity\Resource\ResourceInstance;

/**
 * Utility class gathering conversion methods for Doctrine entities.
 */
class EntityConverter
{
    /** @var EntityManager */
    private $em;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Converts an entity to a standard class object, changing the visibility of
     * all its relevant attributes to public.
     *
     * @param object $entity
     *
     * @return stdClass
     */
    public function toStdClass($entity)
    {
        if ($entity instanceof ResourceInstance) {
            return $this->prepareResourceInstance($entity);
        }

        throw new RuntimeException('Unable to convert entity of type : ' . get_class($entity));
    }

    /**
     * Converts an entity to a JSON string.
     *
     * @param object $entity
     *
     * @return string
     */
    public function toJson($entity)
    {
        return json_encode($this->toStdClass($entity));
    }

    private function prepareResourceInstance(ResourceInstance $instance)
    {
        $preparedInstance = new stdClass();
        $preparedInstance->id = $instance->getId();
        $preparedInstance->name = $instance->getName();
        $preparedInstance->created = $instance->getCreationDate()->format('d-m-Y H:i:s');
        $preparedInstance->updated = $instance->getModificationDate()->format('d-m-Y H:i:s');;
        $preparedInstance->lft = $instance->getLft();
        $preparedInstance->lvl = $instance->getLvl();
        $preparedInstance->rgt = $instance->getRgt();
        $preparedInstance->root = $instance->getRoot();
        $preparedInstance->parent_id = $instance->getParent() != null ? $instance->getParent()->getId() : null;
        $preparedInstance->workspace_id = $instance->getWorkspace()->getId();
        $preparedInstance->resource_id = $instance->getResource()->getId();
        $preparedInstance->instanceCreator_id = $instance->getCreator()->getId();
        $preparedInstance->instance_creator_username = $instance->getCreator()->getUsername();
        $preparedInstance->resource_creator_id = $instance->getResource()->getCreator()->getId();
        $preparedInstance->resource_creator_username = $instance->getResource()->getCreator()->getUsername();
        $preparedInstance->resource_type_id = $instance->getResource()->getResourceType()->getId();
        $preparedInstance->type = $instance->getResource()->getResourceType()->getType();
        $preparedInstance->is_navigable = $instance->getResourceType()->getNavigable();
        $preparedInstance->small_icon = $instance->getResource()->getIcon()->getSmallIcon();
        $preparedInstance->large_icon = $instance->getResource()->getIcon()->getLargeIcon();

        $repo = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance');
        $nodes = $repo->getPath($instance);
        $path = '';

        foreach ($nodes as $node) {
            $path .= "{$node->getName()} /";
        }

        $preparedInstance->path = $path;

        return $preparedInstance;
    }
}