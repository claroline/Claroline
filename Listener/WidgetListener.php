<?php

namespace Icap\PortfolioBundle\Listener;

use Icap\PortfolioBundle\Event\WidgetFormViewEvent;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service()
 */
class WidgetListener
{
    /** @var EngineInterface  */
    protected $templatingEngine;

    /**
     * @DI\InjectParams({
     *     "templatingEngine" = @DI\Inject("templating")
     * })
     */
    public function __construct(EngineInterface $templatingEngine)
    {
        $this->templatingEngine = $templatingEngine;
    }

    /**
     * @param WidgetFormViewEvent $widgetFormEvent
     *
     * @return string
     */
    protected function getFormView(WidgetFormViewEvent $widgetFormEvent)
    {
        return $this->templatingEngine->render('IcapPortfolioBundle:templates/form:' . $widgetFormEvent->getWidgetType() . '.html.twig');
    }

    /**
     * @param WidgetFormViewEvent $widgetFormEvent
     *
     * @DI\Observe("icap_portfolio_widget_form_view_userInformation")
     */
    public function onWidgetFormUserInfomation(WidgetFormViewEvent $widgetFormEvent)
    {
        $widgetFormEvent->setFormView($this->getFormView($widgetFormEvent));
    }

    /**
     * @param WidgetFormViewEvent $widgetFormEvent
     *
     * @DI\Observe("icap_portfolio_widget_form_view_text")
     */
    public function onWidgetFormText(WidgetFormViewEvent $widgetFormEvent)
    {
        $widgetFormEvent->setFormView($this->getFormView($widgetFormEvent));
    }

    /**
     * @param WidgetFormViewEvent $widgetFormEvent
     *
     * @DI\Observe("icap_portfolio_widget_form_view_skills")
     */
    public function onWidgetFormSkills(WidgetFormViewEvent $widgetFormEvent)
    {
        $widgetFormEvent->setFormView($this->getFormView($widgetFormEvent));
    }

    /**
     * @param WidgetFormViewEvent $widgetFormEvent
     *
     * @DI\Observe("icap_portfolio_widget_form_view_formations")
     */
    public function onWidgetFormFormations(WidgetFormViewEvent $widgetFormEvent)
    {
        $widgetFormEvent->setFormView($this->getFormView($widgetFormEvent));
    }

    /**
     * @param WidgetFormViewEvent $widgetFormEvent
     *
     * @DI\Observe("icap_portfolio_widget_form_view_experience")
     */
    public function onWidgetFormExperience(WidgetFormViewEvent $widgetFormEvent)
    {
        $widgetFormEvent->setFormView($this->getFormView($widgetFormEvent));
    }
}
