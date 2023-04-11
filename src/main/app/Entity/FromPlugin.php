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
     */
    protected ?Plugin $plugin = null;

    public function getPlugin(): ?Plugin
    {
        return $this->plugin;
    }

    public function setPlugin(Plugin $plugin): void
    {
        $this->plugin = $plugin;
    }
}
