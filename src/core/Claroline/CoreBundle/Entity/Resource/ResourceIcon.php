<?php

namespace Claroline\CoreBundle\Entity\Resource;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Claroline\CoreBundle\Entity\Resource\IconType;

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
    private $largeIcon;

    /**
     * @ORM\Column(type="string", name="icon")
     */

    private $smallIcon;
    /**
     * @ORM\Column(type="string", name="type")
     */
    private $type;

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

    public function getLargeIcon()
    {
        return $this->largeIcon;
    }

    public function setLargeIcon($largeIcon)
    {
        $this->largeIcon = $largeIcon;
    }

    public function getSmallIcon()
    {
        return $this->smallIcon;
    }

    public function setSmallIcon($smallIcon)
    {
        $this->smallIcon = $smallIcon;
    }

    public function getAbstractResources()
    {
        return $this->abstractResources;
    }

    public function addAbstractResource($abstractResource)
    {
        $this->abstractResource->add($abstractResource);
    }

    public function setIconType(IconType $iconType)
    {
        $this->iconType = $iconType;
    }

    public function getIconType()
    {
        return $this->iconType;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }
}