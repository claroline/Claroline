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

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\CoreBundle\Entity\Plugin;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Resource\ResourceTypeRepository")
 * @ORM\Table(name="claro_resource_type")
 */
class ResourceType
{
    use Id;

    /**
     * @ORM\Column(unique=true)
     */
    private $name;

    /**
     * The entity class of resources of this type.
     *
     * @var string
     *
     * @ORM\Column(length=256)
     */
    private $class;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\MaskDecoder",
     *     mappedBy="resourceType",
     *     cascade={"persist"}
     * )
     *
     * @var ArrayCollection|MaskDecoder[]
     *
     * @todo : we may remove it after checking it's not used
     */
    private $maskDecoders;

    /**
     * @ORM\Column(name="is_exportable", type="boolean")
     */
    private $exportable = false;

    /**
     * A list of tags to group similar types.
     *
     * @ORM\Column(type="json_array")
     *
     * @var array
     */
    private $tags = [];

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Plugin")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $plugin;

    /**
     * @ORM\Column(type="integer")
     */
    private $defaultMask = 1;

    /**
     * @ORM\Column(name="is_enabled", type="boolean")
     */
    private $isEnabled = true;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceRights",
     *     mappedBy="resourceTypes"
     * )
     *
     * @todo find a way to remove it (it's used in some DQL queries)
     */
    protected $rights;

    /**
     * ResourceType constructor.
     */
    public function __construct()
    {
        $this->maskDecoders = new ArrayCollection();
        $this->rights = new ArrayCollection();
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

    /**
     * Returns the resource class name.
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Sets the resource class name.
     *
     * @param string $class
     */
    public function setClass($class)
    {
        $this->class = $class;
    }

    /**
     * Get tags.
     *
     * @return array
     */
    public function getTags()
    {
        return $this->tags;
    }

    public function setTags(array $tags)
    {
        $this->tags = $tags;
    }

    public function setPlugin(Plugin $plugin)
    {
        $this->plugin = $plugin;
    }

    public function getPlugin()
    {
        return $this->plugin;
    }

    public function setExportable($exportable)
    {
        $this->exportable = $exportable;
    }

    public function isExportable()
    {
        return $this->exportable;
    }

    /**
     * @return MaskDecoder[]|ArrayCollection
     */
    public function getMaskDecoders()
    {
        return $this->maskDecoders;
    }

    public function addMaskDecoder(MaskDecoder $maskDecoder)
    {
        if (!$this->maskDecoders->contains($maskDecoder)) {
            $this->maskDecoders->add($maskDecoder);
        }
    }

    public function removeMaskDecoder(MaskDecoder $maskDecoder)
    {
        if ($this->maskDecoders->contains($maskDecoder)) {
            $this->maskDecoders->removeElement($maskDecoder);
        }
    }

    public function setDefaultMask($mask)
    {
        $this->defaultMask = $mask;
    }

    public function getDefaultMask()
    {
        return $this->defaultMask;
    }

    /**
     * @param $isEnabled
     *
     * @deprecated
     */
    public function setIsEnabled($isEnabled)
    {
        $this->setEnabled($isEnabled);
    }

    public function setEnabled($enabled)
    {
        $this->isEnabled = $enabled;
    }

    public function isEnabled()
    {
        return $this->isEnabled;
    }
}
