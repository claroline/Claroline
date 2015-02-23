<?php

namespace Icap\BadgeBundle\Listener;

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
     * @param \Claroline\CoreBundle\Menu\ConfigureMenuEvent $event
     */
    public function onMenuConfigure(ConfigureMenuEvent $event)
    {
        $menu = $event->getMenu();

        $menu
            ->addChild(
                $this->translator->trans('my_badges', array(), 'icap_badge'),
                array(
                    'route' => 'icap_badge_profile_view_badges'
                )
            )
            ->setAttribute('class', 'dropdown')
            ->setAttribute('role', 'presentation')
            ->setExtra('icon', 'fa fa-trophy');
    }
}
