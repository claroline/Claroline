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

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\EventDispatcher\Event;

class WorkspaceAdditionalActionEvent extends Event
{
    private $factory;
    private $menu;
    private $workspace;

    /**
     * @param \Knp\Menu\FactoryInterface                      $factory
     * @param \Knp\Menu\ItemInterface                         $menu
     * @param Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     */
    public function __construct(
        FactoryInterface $factory,
        ItemInterface $menu,
        Workspace $workspace
    ) {
        $this->factory = $factory;
        $this->menu = $menu;
        $this->workspace = $workspace;
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
     * @return Claroline\CoreBundle\Entity\Workspace\Workspace
     */
    public function getWorkspace()
    {
        return $this->workspace;
    }
}
