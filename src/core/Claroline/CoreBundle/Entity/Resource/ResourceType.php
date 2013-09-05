<?php

namespace Claroline\CoreBundle\Entity\Resource;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Claroline\CoreBundle\Entity\Plugin;

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
     * @return integer
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

    public function getMetaTypes()
    {
        return $this->metaTypes;
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
}
