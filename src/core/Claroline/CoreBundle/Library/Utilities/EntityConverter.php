<?php

namespace Claroline\CoreBundle\Library\Utilities;

use \stdClass;
use \RuntimeException;
use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;

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
        if ($entity instanceof AbstractResource) {
            return $this->prepareResourceEntity($entity);
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

    private function prepareResourceEntity(AbstractResource $resource)
    {
        $preparedInstance = new stdClass();
        $preparedInstance->{'id'} = $resource->getId();
        $preparedInstance->{'name'} = $resource->getName();
        $preparedInstance->{'created'} = $resource->getCreationDate()->format('d-m-Y H:i:s');
        $preparedInstance->{'updated'} = $resource->getModificationDate()->format('d-m-Y H:i:s');
        $preparedInstance->{'lvl'} = $resource->getLvl();
        $preparedInstance->{'parent_id'} = $resource->getParent() != null ? $resource->getParent()->getId() : null;
        $preparedInstance->{'workspace_id'} = $resource->getWorkspace()->getId();
        $preparedInstance->{'creator_id'} = $resource->getCreator()->getId();
        $preparedInstance->{'creator_username'} = $resource->getCreator()->getUsername();
        $preparedInstance->{'resource_type_id'} = $resource->getResourceType()->getId();
        $preparedInstance->{'type'} = $resource->getResourceType()->getName();
        $preparedInstance->{'is_navigable'} = $resource->getResourceType()->getBrowsable();
        $preparedInstance->{'small_icon'} = $resource->getIcon()->getSmallIcon();
        $preparedInstance->{'large_icon'} = $resource->getIcon()->getLargeIcon();
        $preparedInstance->{'path'} = $resource->getPath();
        $preparedInstance->{'path_for_display'} = $resource->getPathForDisplay();

        return $preparedInstance;
    }
}