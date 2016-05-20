<?php

namespace Icap\BadgeBundle\Manager;

use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Doctrine\ORM\EntityManager;
use Icap\BadgeBundle\Entity\Widget\BadgeUsageConfig;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("icap_badge.manager.badge_widget")
 */
class BadgeWidgetManager
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * @DI\InjectParams({
     *     "entityManager" = @DI\Inject("doctrine.orm.entity_manager")
     * })
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param WidgetInstance $instance
     *
     * @return BadgeUsageConfig
     */
    public function getBadgeUsageConfigForInstance(WidgetInstance $instance)
    {
        $badgeUsageWidgetConfig = $this->entityManager->getRepository('IcapBadgeBundle:Widget\BadgeUsageConfig')->findOneByWidgetInstance($instance);

        if (null === $badgeUsageWidgetConfig) {
            $badgeUsageWidgetConfig = $this->getDefaultBadgeUsageConfig();
            $badgeUsageWidgetConfig->setWidgetInstance($instance);
        }

        return $badgeUsageWidgetConfig;
    }

    /**
     * @return BadgeUsageConfig
     */
    private function getDefaultBadgeUsageConfig()
    {
        $defaultBadgeUsageConfig = new BadgeUsageConfig();
        $defaultBadgeUsageConfig
            ->setNumberLastAwardedBadge(10)
            ->setNumberMostAwardedBadge(3);
        $defaultBadgeUsageConfig->setSimpleView(false);

        return $defaultBadgeUsageConfig;
    }
}
