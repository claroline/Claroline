<?php

namespace Claroline\CoreBundle\Entity\Resource;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_resource_icon_type")
 */
class IconType
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", name="icon_type")
     */
    private $iconType;

    public function getId()
    {
        return $this->id;
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