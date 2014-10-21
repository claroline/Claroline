<?php

namespace Icap\PortfolioBundle\Factory;

use Icap\PortfolioBundle\Entity\Portfolio;
use Icap\PortfolioBundle\Manager\WidgetTypeManager;
use Icap\PortfolioBundle\Repository\Widget\AbstractWidgetRepository;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("icap_portfolio.factory.widget")
 */
class WidgetFactory
{
    /** @var AbstractWidgetRepository  */
    protected $abstractWidgetRepository;

    /** @var WidgetTypeManager  */
    protected $widgetTypeManager;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "abstractWidgetRepository" = @DI\Inject("icap_portfolio.repository.widget"),
     *     "widgetTypeManager"        = @DI\Inject("icap_portfolio.manager.widget_type")
     * })
     */
    public function __construct(AbstractWidgetRepository $abstractWidgetRepository, WidgetTypeManager $widgetTypeManager)
    {
        $this->abstractWidgetRepository = $abstractWidgetRepository;
        $this->widgetTypeManager        = $widgetTypeManager;
    }

    /**
     * @param Portfolio $portfolio
     * @param string    $widgetType
     *
     * @return \Icap\PortfolioBundle\Entity\Widget\AbstractWidget
     */
    public function createWidget(Portfolio $portfolio, $widgetType)
    {
        if ($this->widgetTypeManager->isWidgetTypeExists($widgetType)) {
            $widgetNamespace = sprintf('Icap\PortfolioBundle\Entity\Widget\%sWidget', ucfirst($widgetType));
            /** @var \Icap\PortfolioBundle\Entity\Widget\AbstractWidget $widget */
            $widget = new $widgetNamespace();
            $widget->setPortfolio($portfolio);

            $maxRow = $this->abstractWidgetRepository->findMaxRow($widget->getPortfolio(), $widget->getColumn());
            $widget->setRow($maxRow['maxRow'] + 1);

            return $widget;
        }

        throw new \InvalidArgumentException("Unknown type of widget.");
    }
}
 