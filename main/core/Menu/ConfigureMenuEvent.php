<?php

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
     * ConfigureMenuEvent constructor.
     *
     * @param FactoryInterface $factory
     * @param ItemInterface    $menu
     * @param Tool             $tool
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
     * @return FactoryInterface
     */
    public function getFactory()
    {
        return $this->factory;
    }

    /**
     * @return ItemInterface
     */
    public function getMenu()
    {
        return $this->menu;
    }

    /**
     * @return Tool
     */
    public function getTool()
    {
        return $this->tool;
    }
}
