<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
     * @ORM\Column(name="group_name", nullable=true)
     */
    protected $group;

    /**
     * @ORM\Column(name="icon", nullable=true)
     */
    protected $icon;

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

    /**
     * @param string $name
     *
     * @return MenuAction
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function isAsync()
    {
        return $this->isAsync;
    }

    /**
     * @param bool $async
     *
     * @return MenuAction
     */
    public function setAsync($async)
    {
        $this->isAsync = $async;

        return $this;
    }

    public function getResourceType()
    {
        return $this->resourceType;
    }

    /**
     * @param ResourceType $resourceType
     *
     * @return MenuAction
     */
    public function setResourceType(ResourceType $resourceType = null)
    {
        $this->resourceType = $resourceType;

        return $this;
    }

    /**
     * @param $value
     *
     * @return MenuAction
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param bool $bool
     *
     * @return MenuAction
     */
    public function setIsCustom($bool)
    {
        $this->isCustom = $bool;

        return $this;
    }

    public function isCustom()
    {
        return $this->isCustom;
    }

    /**
     * @param bool $bool
     *
     * @return MenuAction
     */
    public function setIsForm($bool)
    {
        $this->isForm = $bool;

        return $this;
    }

    public function isForm()
    {
        return $this->isForm;
    }

    public function setGroup($group)
    {
        $this->group = $group;
    }

    public function getGroup()
    {
        return $this->group;
    }

    public function setIcon($icon)
    {
        $this->icon = $icon;
    }

    public function getIcon()
    {
        return $this->icon;
    }
}
