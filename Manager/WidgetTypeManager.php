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
     * @return \Icap\PortfolioBundle\Entity\Widget\WidgetType[]
     */
    public function getWidgetsTypes()
    {
        $widgetTypes = $this->widgetTypeRepository->findAllInArray();
        $sortedWidgetTypes = array();

        foreach ($widgetTypes as $widgetType) {
            $sortedWidgetTypes[$widgetType['name']] = $widgetType;
        }

        return $sortedWidgetTypes;
    }

    /**
     * @return array
     */
    public function getWidgetTabOrder()
    {
        return [
            'userInformation' => 0,
            'formations' => 1,
            'experience' => 2,
            'skills' => 3,
            'text' => 4,
        ];
    }

    /**
     * @return \Icap\PortfolioBundle\Entity\Widget\WidgetType[]
     */
    public function getWidgetsTypesForDisplay()
    {
        $widgetTypes = $this->getWidgetsTypes();
        $sortedWidgetTypes = array();
        $widgetTabOrder = $this->getWidgetTabOrder();

        foreach ($widgetTypes as $widgetType) {
            $widgetTabOrder = isset($widgetTabOrder[$widgetType['name']]) ? $widgetTabOrder[$widgetType['name']] : count($sortedWidgetTypes);
            $sortedWidgetTypes[$widgetTabOrder] = $widgetType;
        }

        ksort($sortedWidgetTypes);

        return $sortedWidgetTypes;
    }

    /**
     * @return array
     */
    public function getWidgetsConfig()
    {
        return $this->getWidgetsTypes();
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
 