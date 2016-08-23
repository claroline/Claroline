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

use Claroline\CoreBundle\Entity\Plugin;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\ResourceTypeRepository")
 * @ORM\Table(name="claro_resource_type")
 */
class ResourceType
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(unique=true)
     */
    protected $name;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode",
     *     mappedBy="resourceType",
     *     cascade={"persist"}
     * )
     */
    protected $abstractResources;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\MaskDecoder",
     *     mappedBy="resourceType",
     *     cascade={"persist"}
     * )
     */
    protected $maskDecoders;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\MenuAction",
     *     mappedBy="resourceType",
     *     cascade={"persist"}
     * )
     */
    protected $actions;

    /**
     * @ORM\Column(name="is_exportable", type="boolean")
     */
    protected $isExportable = false;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Plugin")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $plugin;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceRights",
     *     mappedBy="resourceTypes"
     * )
     */
    protected $rights;

    /**
     * @ORM\Column(type="integer")
     */
    protected $defaultMask = 1;

    /**
     * @ORM\Column(name="is_enabled", type="boolean")
     */
    protected $isEnabled = true;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->abstractResources = new ArrayCollection();
        $this->actions = new ArrayCollection();
    }

    /**
     * Returns the resource type id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the resource type name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the resource type name.
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    public function setPlugin(Plugin $plugin)
    {
        $this->plugin = $plugin;
    }

    public function getPlugin()
    {
        return $this->plugin;
    }

    public function getActions()
    {
        return $this->actions;
    }

    public function addAction(MenuAction $action)
    {
        $this->actions->add($action);
    }

    public function getAbstractResources()
    {
        return $this->abstractResources;
    }

    public function addAbstractResource($abstractResource)
    {
        $this->abstractResource->add($abstractResource);
    }

    public function setExportable($isExportable)
    {
        $this->isExportable = $isExportable;
    }

    public function isExportable()
    {
        return $this->isExportable;
    }

    public function getMaskDecoders()
    {
        return $this->maskDecoders;
    }

    public function setDefaultMask($mask)
    {
        $this->defaultMask = $mask;
    }

    public function getDefaultMask()
    {
        return $this->defaultMask;
    }

    public function setIsEnabled($isEnabled)
    {
        $this->isEnabled = $isEnabled;
    }

    public function isEnabled()
    {
        return $this->isEnabled;
    }
}
