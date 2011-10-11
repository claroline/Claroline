<?php

namespace Claroline\PluginBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Claroline\PluginBundle\Entity\ApplicationLauncher;

/**
 * @ORM\Entity(repositoryClass="Claroline\PluginBundle\Repository\PluginRepository")
 * @ORM\Table(name="claro_application")
 */
class Application extends Plugin
{
    /**
     * @ORM\OneToMany(targetEntity="ApplicationLauncher", mappedBy="application", cascade={"persist", "remove"})
     */
    private $launchers;

    public function __construct()
    {
        $this->launchers = new ArrayCollection();
    }

    public function getLaunchers()
    {
        return $this->launchers->toArray();
    }

    public function addLauncher(ApplicationLauncher $launcher)
    {
        $this->launchers->add($launcher);
    }
}