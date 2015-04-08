<?php

namespace Icap\PortfolioBundle\Listener;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Menu\ConfigureMenuEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Translation\Translator;

/**
 * @DI\Service()
 */
class ConfigureTopRightMenuListener
{
    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var PlatformConfigurationHandler
     */
    private $platformConfigHandler;

    /**
     * @DI\InjectParams({
     *     "translator" = @DI\Inject("translator"),
     *     "platformConfigHandler" = @DI\Inject("claroline.config.platform_config_handler")
     * })
     */
    public function __construct(Translator $translator, PlatformConfigurationHandler $platformConfigHandler)
    {
        $this->translator = $translator;
        $this->platformConfigHandler = $platformConfigHandler;
    }

    /**
     * @DI\Observe("claroline_top_bar_right_menu_configure_desktop_tool_my_portfolios")
     *
     * @param \Claroline\CoreBundle\Menu\ConfigureMenuEvent $event
     */
    public function onMenuConfigure(ConfigureMenuEvent $event)
    {
        $menuItemConfig = ['route' => 'icap_portfolio_list'];

        if ($this->platformConfigHandler->getParameter('portfolio_url')) {
            $menuItemConfig = ['uri' => $this->platformConfigHandler->getParameter('portfolio_url')];
        }

        $menu = $event->getMenu();

        $menu
            ->addChild(
                $this->translator->trans('my_portfolios', array(), 'icap_portfolio'),
                $menuItemConfig
            )
            ->setAttribute('class', 'dropdown')
            ->setAttribute('role', 'presentation')
            ->setExtra('icon', 'fa fa-list-alt');
    }
}
