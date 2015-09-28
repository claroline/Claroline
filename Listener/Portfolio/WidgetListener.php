<?php

namespace Icap\BadgeBundle\Listener\Portfolio;

use Icap\BadgeBundle\Factory\Portfolio\WidgetFactory;
use Icap\PortfolioBundle\Event\WidgetDataEvent;
use Icap\PortfolioBundle\Event\WidgetFormViewEvent;
use Icap\PortfolioBundle\Event\WidgetViewEvent;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use JMS\DiExtraBundle\Annotation as DI;

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
     * @DI\InjectParams({
     *     "templatingEngine" = @DI\Inject("templating"),
     *     "widgetFactory" = @DI\Inject("icap_badge.factory.portfolio_widget")
     * })
     */
    public function __construct(EngineInterface $templatingEngine, WidgetFactory $widgetFactory)
    {
        $this->templatingEngine = $templatingEngine;
        $this->widgetFactory = $widgetFactory;
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
}
