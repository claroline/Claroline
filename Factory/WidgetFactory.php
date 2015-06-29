<?php

namespace Icap\PortfolioBundle\Factory;

use Icap\PortfolioBundle\Entity\Portfolio;
use Icap\PortfolioBundle\Entity\PortfolioWidget;
use Icap\PortfolioBundle\Manager\WidgetTypeManager;
use Icap\PortfolioBundle\Repository\Widget\AbstractWidgetRepository;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Service("icap_portfolio.factory.widget")
 */
class WidgetFactory
{
    /** @var WidgetTypeManager  */
    protected $widgetTypeManager;

    /** @var TranslatorInterface  */
    protected $translator;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "widgetTypeManager"        = @DI\Inject("icap_portfolio.manager.widget_type"),
     *     "translator"               = @DI\Inject("translator")
     * })
     */
    public function __construct(WidgetTypeManager $widgetTypeManager, TranslatorInterface $translator)
    {
        $this->widgetTypeManager        = $widgetTypeManager;
        $this->translator               = $translator;
    }

    /**
     * @param string $widgetType
     *
     * @return \Icap\PortfolioBundle\Entity\Widget\AbstractWidget
     */
    public function createDataWidget($widgetType)
    {
        if ($this->widgetTypeManager->isWidgetTypeExists($widgetType)) {
            $widgetNamespace = sprintf('Icap\PortfolioBundle\Entity\Widget\%sWidget', ucfirst($widgetType));
            /** @var \Icap\PortfolioBundle\Entity\Widget\AbstractWidget $widget */
            $widget = new $widgetNamespace();
            $widget
                ->setLabel($this->translator->trans($widgetType . '_title', array(), 'icap_portfolio'));

            return $widget;
        }

        throw new \InvalidArgumentException("Unknown type of widget.");
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
 