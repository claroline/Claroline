<?php

namespace Icap\PortfolioBundle\Manager;

use Icap\PortfolioBundle\Entity\Widget\AbstractWidget;
use Icap\PortfolioBundle\Factory\WidgetFactory;
use Icap\PortfolioBundle\Repository\Widget\WidgetTypeRepository;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("icap_portfolio.manager.widget_type")
 */
class WidgetTypeManager
{
    /** @var WidgetTypeRepository  */
    protected $widgetTypeRepository;

    /**
     * @DI\InjectParams({
     *     "widgetTypeRepository" = @DI\Inject("icap_portfolio.repository.widget_type")
     * })
     */
    public function __construct(WidgetTypeRepository $widgetTypeRepository)
    {
        $this->widgetTypeRepository = $widgetTypeRepository;
    }

    /**
     * @return array
     */
    public function getWidgetsTypes()
    {
        $widgetTypes       = $this->widgetTypeRepository->findAllInArray();
        $sortedWidgetTypes = array();
²
        foreach ($widgetTypes as $widgetType) {
            if ($widgetType['name'] !== 'title') {
                $sortedWidgetTypes[$widgetType['name']] = $widgetType;
            }
        }

        return $sortedWidgetTypes;
    }

    /**
     * @return array
     */
    public function getWidgetsConfig()
    {
        $widgetTypes       = $this->widgetTypeRepository->findAllInArray();
        $sortedWidgetTypes = array();

        foreach ($widgetTypes as $widgetType) {
            $sortedWidgetTypes[$widgetType['name']] = $widgetType;
        }

        return $sortedWidgetTypes;
    }

    /**
     * @param string $widgetType
     *
     * @return bool
     */
    public function isWidgetTypeExists($widgetType)
    {
        $widgetsConfig = $this->getWidgetsConfig();
        $isWidgetTypeExists = false;

        if (isset($widgetsConfig[$widgetType])) {
            $isWidgetTypeExists = true;
        }

        return $isWidgetTypeExists;
    }
}
 