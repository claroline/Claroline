<?php

namespace Icap\BadgeBundle\Factory\Portfolio;

use Icap\BadgeBundle\Entity\Portfolio\BadgesWidget;
use Icap\PortfolioBundle\Entity\Widget\WidgetType;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Service("icap_badge.factory.portfolio_widget")
 */
class WidgetFactory
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @DI\InjectParams({
     *     "translator" = @DI\Inject("translator")
     * })
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param string $widgetType
     *
     * @return \Icap\BadgeBundle\Entity\Portfolio\BadgesWidget
     */
    public function createEmptyDataWidget($widgetType)
    {
        $badgesWidget = new BadgesWidget();
        $badgesWidget
            ->setLabel($this->translator->trans($widgetType.'_title', array(), 'icap_portfolio'));

        return $badgesWidget;
    }

    /**
     * @return WidgetType
     */
    public function createBadgeWidgetType()
    {
        $widgetType = new WidgetType();

        return $widgetType
            ->setName('badges')
            ->setIcon('trophy');
    }
}
