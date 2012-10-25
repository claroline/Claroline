<?php

namespace Claroline\CoreBundle\Entity\Widget;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_widget_display_options")
 */
class WidgetAdminOption
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $name;

    const LOCK_VISIBLE = 1;
    const LOCK_UNVISIBLE = 2;
    const CONFIGURABLE_VISIBLE = 3;
    const CONFIGURABLE_UNVISIBLE = 4;

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
}
