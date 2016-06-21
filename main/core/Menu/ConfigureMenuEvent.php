<?php
// src/Acme/DemoBundle/Event/ConfigureMenuEvent.php

namespace Claroline\CoreBundle\Menu;

use Claroline\CoreBundle\Entity\Tool\Tool;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\EventDispatcher\Event;

class ConfigureMenuEvent extends Event
{
    private $factory;
    private $menu;
    private $tool;

    /**
     * @param \Knp\Menu\FactoryInterface $factory
     * @param \Knp\Menu\ItemInterface    $menu
     */
    public function __construct(
        FactoryInterface $factory,
        ItemInterface $menu,
        Tool $tool = null
    ) {
        $this->factory = $factory;
        $this->menu = $menu;
        $this->tool = $tool;
    }

    /**
     * @return \Knp\Menu\FactoryInterface
     */
    public function getFactory()
    {
        return $this->factory;
    }

    /**
     * @return \Knp\Menu\ItemInterface
     */
    public function getMenu()
    {
        return $this->menu;
    }

    /**
     * @return \Claroline\CoreBundle\Entity\Tool
     */
    public function getTool()
    {
        return $this->tool;
    }
}
