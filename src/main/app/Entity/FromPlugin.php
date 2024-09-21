<?php

namespace Claroline\AppBundle\Entity;

use Claroline\CoreBundle\Entity\Plugin;
use Doctrine\ORM\Mapping as ORM;

trait FromPlugin
{
    /**
     * The plugin that have introduced the entity.
     */
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: \Claroline\CoreBundle\Entity\Plugin::class)]
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
