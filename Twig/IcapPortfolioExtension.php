<?php
namespace Icap\PortfolioBundle\Twig;

use Icap\PortfolioBundle\Entity\Portfolio;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Tag;

/**
 * @Service("icap.portfolio.twig.extension")
 * @Tag("twig.extension")
 */
class IcapPortfolioExtension extends \Twig_Extension
{
    public function getName()
    {
        return 'icap_portfolio';
    }

    public function getFilters()
    {
        return array(
            'visibilityLabel'  => new \Twig_Filter_Method($this, 'getVisibilityLabel'),
            'orderByRow'       => new \Twig_Filter_Method($this, 'getOrder')
        );
    }

    public function getVisibilityLabel($visibility)
    {
        return Portfolio::getVisibilityLabels()[$visibility];
    }

    /**
     * @param \Icap\PortfolioBundle\Entity\Widget\AbstractWidget[]$widgets
     *
     * @return mixed
     */
    public function getOrder($widgets)
    {
        $orderedWidgets = array();

        foreach ($widgets as $widget) {
            $orderedWidgets[$widget->getColumn() . $widget->getRow()] = $widget;
        }

        ksort($orderedWidgets);

        return $orderedWidgets;
    }
}
