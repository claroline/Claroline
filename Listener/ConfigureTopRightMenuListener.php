<?php

namespace Icap\PortfolioBundle\Listener;

use Claroline\CoreBundle\Menu\ConfigureMenuEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Translation\Translator;

/**
 * @DI\Service()
 */
class ConfigureTopRightMenuListener
{
    private $translator;

    /**
     * @DI\InjectParams({
     *     "translator" = @DI\Inject("translator"),
     * })
     */
    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @DI\Observe("claroline_top_bar_right_menu_configure")
     *
     * @param \Acme\DemoBundle\Event\ConfigureMenuEvent $event
     */
    public function onMenuConfigure(ConfigureMenuEvent $event)
    {
        $menu = $event->getMenu();

        $menu->addChild(
            $this->translator->trans('my_portfolios', array(), 'platform'),
            array('route' => 'icap_portfolio_list')
        )
            ->setAttribute('class', 'dropdown')
            ->setAttribute('role', 'presentation')
            ->setExtra('icon', 'fa fa-list-alt');
    }
}
