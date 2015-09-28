<?php

namespace Icap\PortfolioBundle\Listener;

use Icap\PortfolioBundle\Event\WidgetDataEvent;
use Icap\PortfolioBundle\Event\WidgetFormViewEvent;
use Icap\PortfolioBundle\Factory\WidgetFactory;
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
     *     "widgetFactory" = @DI\Inject("icap_portfolio.factory.widget")
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
     * @DI\Observe("icap_portfolio_widget_form_view_userInformation")
     * @DI\Observe("icap_portfolio_widget_form_view_text")
     * @DI\Observe("icap_portfolio_widget_form_view_skills")
     * @DI\Observe("icap_portfolio_widget_form_view_formations")
     * @DI\Observe("icap_portfolio_widget_form_view_experience")
     */
    public function onWidgetFormView(WidgetFormViewEvent $widgetFormViewEvent)
    {
        $widgetFormViewEvent->setFormView($this->templatingEngine->render('IcapPortfolioBundle:templates/form:' . $widgetFormViewEvent->getWidgetType() . '.html.twig'));
    }

    /**
     * @param WidgetDataEvent $widgetDataEvent
     *
     * @DI\Observe("icap_portfolio_widget_data_userInformation")
     * @DI\Observe("icap_portfolio_widget_data_text")
     * @DI\Observe("icap_portfolio_widget_data_skills")
     * @DI\Observe("icap_portfolio_widget_data_formations")
     * @DI\Observe("icap_portfolio_widget_data_experience")
     */
    public function onWidgetData(WidgetDataEvent $widgetDataEvent)
    {
        $widgetDataEvent->setWidget($this->widgetFactory->createEmptyDataWidget($widgetDataEvent->getWidgetType()));
    }
}
