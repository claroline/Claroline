<?php

namespace Icap\BadgeBundle\Listener\Portfolio;

use Icap\BadgeBundle\Factory\Portfolio\WidgetFactory;
use Icap\PortfolioBundle\Event\WidgetDataEvent;
use Icap\PortfolioBundle\Event\WidgetFormEvent;
use Icap\PortfolioBundle\Event\WidgetFormViewEvent;
use Icap\PortfolioBundle\Event\WidgetViewEvent;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\FormFactory;

/**
 * @DI\Service()
 */
class WidgetListener
{
    /**
     * @var EngineInterface
     */
    protected $templatingEngine;

    /**
     * @var WidgetFactory
     */
    protected $widgetFactory;

    /**
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * @DI\InjectParams({
     *     "templatingEngine" = @DI\Inject("templating"),
     *     "widgetFactory" = @DI\Inject("icap_badge.factory.portfolio_widget"),
     *     "formFactory" = @DI\Inject("form.factory"),
     * })
     */
    public function __construct(EngineInterface $templatingEngine, WidgetFactory $widgetFactory, FormFactory $formFactory)
    {
        $this->templatingEngine = $templatingEngine;
        $this->widgetFactory = $widgetFactory;
        $this->formFactory = $formFactory;
    }

    /**
     * @param WidgetFormViewEvent $widgetFormViewEvent
     *
     * @DI\Observe("icap_portfolio_widget_form_view_badges")
     */
    public function onWidgetFormView(WidgetFormViewEvent $widgetFormViewEvent)
    {
        $widgetFormViewEvent->setFormView($this->templatingEngine->render('IcapBadgeBundle:Portfolio/form:' . $widgetFormViewEvent->getWidgetType() . '.html.twig'));
    }

    /**
     * @param WidgetDataEvent $widgetDataEvent
     *
     * @DI\Observe("icap_portfolio_widget_data_badges")
     */
    public function onWidgetData(WidgetDataEvent $widgetDataEvent)
    {
        $widgetDataEvent->setWidget($this->widgetFactory->createEmptyDataWidget($widgetDataEvent->getWidgetType()));
    }

    /**
     * @param WidgetViewEvent $widgetViewEvent
     *
     * @DI\Observe("icap_portfolio_widget_view_badges")
     */
    public function onWidgetView(WidgetViewEvent $widgetViewEvent)
    {
        $widgetViewEvent->setView($this->templatingEngine->render('IcapBadgeBundle:Portfolio:' . $widgetViewEvent->getWidgetType() . '.html.twig', array('widget' => $widgetViewEvent->getWidget())));
    }

    /**
     * @param WidgetFormEvent $widgetFormEvent
     *
     * @DI\Observe("icap_portfolio_widget_form_badges")
     */
    public function onWidgetForm(WidgetFormEvent $widgetFormEvent)
    {
        $widgetFormEvent->setForm($this->formFactory->create('icap_badge_portfolio_widget_form_' . $widgetFormEvent->getWidgetType(), $widgetFormEvent->getData()));
    }
}
