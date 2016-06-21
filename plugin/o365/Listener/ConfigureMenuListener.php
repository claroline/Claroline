<?php

namespace FormaLibre\OfficeConnectBundle\Listener;

use Claroline\CoreBundle\Event\RenderAuthenticationButtonEvent;
use Claroline\CoreBundle\Menu\ConfigureMenuEvent;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service()
 */
class ConfigureMenuListener
{
    private $templating;

    /**
     * @DI\InjectParams({
     *     "templating" = @DI\Inject("templating")
     * })
     */
    public function __construct($templating)
    {
        $this->templating = $templating;
    }

    /**
     * @DI\Observe("claroline_external_authentication_menu_configure")
     *
     * @param \Claroline\CoreBundle\Menu\ConfigureMenuEvent $event
     *
     * @return \Knp\Menu\ItemInterface $menu
     */
    public function onTopBarLeftMenuConfigure(ConfigureMenuEvent $event)
    {
        $menu = $event->getMenu();
        $menu->addChild(
            'Office',
            array('route' => 'formalibre_office_form')
        )->setExtra('name', 'office');

        return $menu;
    }

    /**
     * @DI\Observe("render_external_authentication_button")
     *
     * @param RenderAuthenticationButtonEvent $event
     *
     * @return string
     */
    public function onRenderButton(RenderAuthenticationButtonEvent $event)
    {
        $content = $this->templating->render(
            'FormaLibreOfficeConnectBundle::button.html.twig',
            array()
        );

        $event->addContent($content);
    }
}
