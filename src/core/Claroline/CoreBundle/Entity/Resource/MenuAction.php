<?php

namespace Claroline\CoreBundle\Entity\Resource;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_menu_action")
 */
class MenuAction
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(nullable=true)
     */
    protected $name;

    /**
     * @ORM\Column(name="async", type="boolean", nullable=true)
     */
    protected $isAsync;

    /**
     * @ORM\Column(name="is_custom", type="boolean", nullable=false)
     */
    protected $isCustom = true;

    /**
     * @ORM\Column(name="is_form", type="boolean", nullable=false)
     */
    protected $isForm = false;

    /**
     * @ORM\Column(name="value", nullable=true)
     */
    protected $value;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceType",
     *     inversedBy="actions",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="resource_type_id", onDelete="SET NULL")
     */
    protected $resourceType;

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function isAsync()
    {
        return $this->isAsync;
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

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setIsCustom($bool)
    {
        $this->isCustom = $bool;
    }

    public function isCustom()
    {
        return $this->isCustom;
    }

    public function setIsForm($bool)
    {
        $this->isForm = $bool;
    }

    public function isForm()
    {
        return $this->isForm;
    }
}
