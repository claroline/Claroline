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
    private $plugin;

    /**
     * Get plugin.
     *
     * @return Plugin
     */
    public function getPlugin()
    {
        return $this->plugin;
    }

    /**
     * Set plugin.
     *
     * @param Plugin $plugin
     */
    public function setPlugin(Plugin $plugin)
    {
        $this->plugin = $plugin;
    }
}
