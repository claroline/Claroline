<?php

namespace Claroline\CoreBundle\Entity\Resource;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_resource_icon_type")
 */
class IconType
{
    const TYPE = 1; //resource type
    const GENERATED = 2; //generated (ie png)
    const BASIC_MIME_TYPE = 3; //ie video
    const COMPLETE_MIME_TYPE = 4; //ie video/mp4
    const DEFAULT_ICON = 5; //default
    const CUSTOM_ICON = 6; //defined by the user

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", name="type")
     */
    protected $type;

    public function getId()
    {
        return $this->id;
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