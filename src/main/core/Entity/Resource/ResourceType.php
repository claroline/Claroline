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
     * @ORM\Column(type="json")
     *
     * @var array
     */
    private ?array $tags = [];

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Plugin")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private ?Plugin $plugin = null;

    /**
     * @ORM\Column(name="is_enabled", type="boolean")
     */
    private bool $isEnabled = true;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceRights",
     *     mappedBy="resourceTypes"
     * )
     *
     * @todo find a way to remove it (it's used in some DQL queries)
     */
    protected $rights;

    public function __construct()
    {
        $this->maskDecoders = new ArrayCollection();
        $this->rights = new ArrayCollection();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function setClass(string $class): void
    {
        $this->class = $class;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function setTags(array $tags): void
    {
        $this->tags = $tags;
    }

    public function setPlugin(Plugin $plugin): void
    {
        $this->plugin = $plugin;
    }

    public function getPlugin(): ?Plugin
    {
        return $this->plugin;
    }

    public function setExportable(bool $exportable): void
    {
        $this->exportable = $exportable;
    }

    public function isExportable(): bool
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

    public function addMaskDecoder(MaskDecoder $maskDecoder): void
    {
        if (!$this->maskDecoders->contains($maskDecoder)) {
            $this->maskDecoders->add($maskDecoder);
        }
    }

    public function removeMaskDecoder(MaskDecoder $maskDecoder): void
    {
        if ($this->maskDecoders->contains($maskDecoder)) {
            $this->maskDecoders->removeElement($maskDecoder);
        }
    }

    public function setEnabled(bool $enabled): void
    {
        $this->isEnabled = $enabled;
    }

    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }
}
