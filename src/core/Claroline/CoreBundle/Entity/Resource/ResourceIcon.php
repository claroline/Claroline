<?php

namespace Claroline\CoreBundle\Entity\Resource;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_resource_icon")
 */
class ResourceIcon
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", name="thumbnail")
     */
    private $thumbnail;

    /**
     * @ORM\Column(type="string", name="icon")
     */
    private $icon;

    /**
     * @ORM\OneToMany(
     *      targetEntity="Claroline\CoreBundle\Entity\Resource\AbstractResource",
     *      mappedBy="images",
     *      cascade={"persist"}
     * )
     */
    private $abstractResources;

    /**
     * @ORM\OneToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\IconType")
     * @ORM\JoinColumn(name="icon_type_id", referencedColumnName="id")
     */
    private $iconType;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->abstractResources = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getThumbnail()
    {
        return $this->thumbnail;
    }

    public function setThumbnail($thumbnail)
    {
        $this->thumbnail = $thumbnail;
    }

    public function getIcon()
    {
        return $this->icon;
    }

    public function setIcon($icon)
    {
        $this->icon = $icon;
    }

    public function getAbstractResources()
    {
        return $this->abstractResources;
    }

    public function addAbstractResource($abstractResource)
    {
        $this->abstractResource->add($abstractResource);
    }

    public function setIconType($iconType)
    {
        $this->iconType = $iconType;
    }

    public function getIconType()
    {
        return $this->iconType;
    }
}