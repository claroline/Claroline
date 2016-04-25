<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Menu;

use Claroline\CoreBundle\Entity\Group;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\EventDispatcher\Event;

class GroupAdditionalActionEvent extends Event
{
    private $factory;
    private $menu;
    private $group;

    /**
     * @param \Knp\Menu\FactoryInterface        $factory
     * @param \Knp\Menu\ItemInterface           $menu
     * @param Claroline\CoreBundle\Entity\Group $group
     */
    public function __construct(
        FactoryInterface $factory,
        ItemInterface $menu,
        Group $group
    ) {
        $this->factory = $factory;
        $this->menu = $menu;
        $this->group = $group;
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
     * @return Claroline\CoreBundle\Entity\Group
     */
    public function getGroup()
    {
        return $this->group;
    }
}
