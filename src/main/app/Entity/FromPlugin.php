<?php

namespace Claroline\AppBundle\Entity;

use Claroline\CoreBundle\Entity\Plugin;
use Doctrine\ORM\Mapping as ORM;

trait FromPlugin
{
    /**
     * The plugin that have introduced the entity.
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Plugin")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var Plugin
     */
    protected $plugin;

    public function getPlugin(): ?Plugin
    {
        return $this->plugin;
    }

    public function setPlugin(Plugin $plugin)
    {
        $this->plugin = $plugin;
    }
}
