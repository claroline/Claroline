<?php

namespace Claroline\CoreBundle\Entity\Resource;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_resource_type_custom_action")
 */
class ResourceTypeCustomAction
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $action;

    /**
     * @ORM\Column(type="string", length=255, name="async")
     */
    private $isAsync;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceType", inversedBy="customActions", cascade={"persist"})
     * @ORM\JoinColumn(name="resource_type_id", referencedColumnName="id")
     */
    private $resourceType;

    public function getId()
    {
        return $this->id;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function setAction($action)
    {
        $this->action = $action;
    }

    public function isAsync()
    {
        return $this->async;
    }

    public function setAsync($async)
    {
        $this->isAsync = $async;
    }

    public function getResourceType()
    {
        return $this->resourceType;
    }

    public function setResourceType($resourceType)
    {
        $this->resourceType = $resourceType;
    }
}