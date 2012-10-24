<?php

namespace Claroline\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_widget")
 */
class Widget
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Plugin")
     * @ORM\JoinColumn(name="plugin_id", referencedColumnName="id")
     */
    protected $plugin;


    /**
     * @ORM\Column(type="string")
     */
    protected $name;

    public function getId()
    {
        return $this->id;
    }

    public function setPlugin($plugin)
    {
        $this->plugin = $plugin;
    }

    public function getPlugin()
    {
        return $this->plugin;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->$name;
    }
}
