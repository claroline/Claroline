<?php

namespace Icap\PortfolioBundle\Factory;

use Icap\PortfolioBundle\Entity\Portfolio;
use Icap\PortfolioBundle\Entity\PortfolioWidget;
use Icap\PortfolioBundle\Event\WidgetDataEvent;
use Icap\PortfolioBundle\Manager\WidgetTypeManager;
use Icap\PortfolioBundle\Repository\Widget\AbstractWidgetRepository;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Service("icap_portfolio.factory.widget")
 */
class WidgetFactory
{
    /**
     * @var WidgetTypeManager
     */
    protected $widgetTypeManager;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @DI\InjectParams({
     *     "widgetTypeManager" = @DI\Inject("icap_portfolio.manager.widget_type"),
     *     "translator" = @DI\Inject("translator"),
     *     "eventDispatcher" = @DI\Inject("event_dispatcher")
     * })
     */
    public function __construct(WidgetTypeManager $widgetTypeManager, TranslatorInterface $translator, EventDispatcherInterface $eventDispatcher)
    {
        $this->widgetTypeManager = $widgetTypeManager;
        $this->translator = $translator;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param string $widgetType
     *
     * @return \Icap\PortfolioBundle\Entity\Widget\AbstractWidget
     */
    public function createDataWidget($widgetType)
    {
        if ($this->widgetTypeManager->isWidgetTypeExists($widgetType)) {
            $widgetDataEvent = new WidgetDataEvent($widgetType);

            $this->eventDispatcher->dispatch('icap_portfolio_widget_data_' . $widgetType, $widgetDataEvent);

            return $widgetDataEvent->getWidget();
        }

        throw new \InvalidArgumentException("Unknown type of widget.");
    }

    /**
     * @param string $widgetType
     *
     * @return \Icap\PortfolioBundle\Entity\Widget\AbstractWidget
     */
    public function createEmptyDataWidget($widgetType)
    {
        $widgetNamespace = sprintf('Icap\PortfolioBundle\Entity\Widget\%sWidget', ucfirst($widgetType));
        /** @var \Icap\PortfolioBundle\Entity\Widget\AbstractWidget $widget */
        $widget = new $widgetNamespace();
        $widget
            ->setLabel($this->translator->trans($widgetType . '_title', array(), 'icap_portfolio'));

        return $widget;
    }

    /**
     * @param Portfolio $portfolio
     *
     * @param string    $type
     *
     * @return \Icap\PortfolioBundle\Entity\PortfolioWidget
     */
    public function createPortfolioWidget(Portfolio $portfolio, $type)
    {
        if (!$this->widgetTypeManager->isWidgetTypeExists($type)) {
            throw new \InvalidArgumentException();
        }

        $portfolioWidget = new PortfolioWidget();
        $portfolioWidget
            ->setPortfolio($portfolio)
            ->setWidgetType($type)
        ;

        return $portfolioWidget;
    }
}
 