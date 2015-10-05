<?php

namespace Icap\PortfolioBundle\Listener;

use Icap\PortfolioBundle\Event\WidgetDataEvent;
use Icap\PortfolioBundle\Event\WidgetFormEvent;
use Icap\PortfolioBundle\Event\WidgetFormViewEvent;
use Icap\PortfolioBundle\Event\WidgetViewEvent;
use Icap\PortfolioBundle\Factory\WidgetFactory;
use Icap\PortfolioBundle\Manager\WidgetsManager;
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
     *     "widgetFactory" = @DI\Inject("icap_portfolio.factory.widget"),
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

    /**
     * @param WidgetViewEvent $widgetViewEvent
     *
     * @DI\Observe("icap_portfolio_widget_view_userInformation")
     * @DI\Observe("icap_portfolio_widget_view_text")
     * @DI\Observe("icap_portfolio_widget_view_skills")
     * @DI\Observe("icap_portfolio_widget_view_formations")
     * @DI\Observe("icap_portfolio_widget_view_experience")
     */
    public function onWidgetView(WidgetViewEvent $widgetViewEvent)
    {
        $widgetViewEvent->setView($this->templatingEngine->render('IcapPortfolioBundle:templates:' . $widgetViewEvent->getWidgetType() . '.html.twig', array('widget' => $widgetViewEvent->getWidget())));
    }

    /**
     * @param WidgetFormEvent $widgetFormEvent
     *
     * @DI\Observe("icap_portfolio_widget_form_userInformation")
     * @DI\Observe("icap_portfolio_widget_form_text")
     * @DI\Observe("icap_portfolio_widget_form_skills")
     * @DI\Observe("icap_portfolio_widget_form_formations")
     * @DI\Observe("icap_portfolio_widget_form_experience")
     */
    public function onWidgetForm(WidgetFormEvent $widgetFormEvent)
    {
        $widgetFormEvent->setForm($this->formFactory->create('icap_portfolio_widget_form_' . $widgetFormEvent->getWidgetType(), $widgetFormEvent->getData()));
    }
}
