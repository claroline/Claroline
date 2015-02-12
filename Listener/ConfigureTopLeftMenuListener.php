<?php

namespace Claroline\CursusBundle\Listener;

use Claroline\CoreBundle\Menu\ConfigureMenuEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Translation\Translator;

/**
 * @DI\Service()
 */
class ConfigureTopLeftMenuListener
{
    private $securityContext;
    private $translator;

    /**
     * @DI\InjectParams({
     *     "securityContext" = @DI\Inject("security.context"),
     *     "translator"      = @DI\Inject("translator")
     * })
     */
    public function __construct(
        SecurityContext $securityContext,
        Translator $translator
    )
    {
        $this->securityContext = $securityContext;
        $this->translator = $translator;
    }

    /**
     * @DI\Observe("claroline_top_bar_left_menu_configure")
     *
     * @param \Acme\DemoBundle\Event\ConfigureMenuEvent $event
     */
    public function onMenuConfigure(ConfigureMenuEvent $event)
    {
        $user = $this->securityContext->getToken()->getUser();

        if ($user !== 'anon.') {
            $menu = $event->getMenu();
            $menu->addChild(
                $this->translator->trans('courses_list', array(), 'cursus'),
                array('route' => 'claro_cursus_tool_course_index')
            )->setExtra('icon', 'fa fa-university')
            ->setExtra(
                'title',
                $this->translator->trans('courses_list', array(), 'cursus')
            );
//
            return $menu;
        }
    }
}
