<?php

namespace Icap\BadgeBundle\Manager;

use Icap\BadgeBundle\Entity\Badge;
use Icap\BadgeBundle\Entity\BadgeRule;
use Icap\BadgeBundle\Entity\UserBadge;
use Icap\BadgeBundle\Entity\Widget\BadgeUsageConfig;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Claroline\CoreBundle\Event\Log\LogBadgeAwardEvent;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\UnitOfWork;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @DI\Service("claroline.manager.badge_widget")
 */
class BadgeWidgetManager
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * Constructor.
     *
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
        $badgeUsageWidgetConfig = $this->entityManager->getRepository('ClarolineCoreBundle:Badge\Widget\BadgeUsageConfig')->findOneByWidgetInstance($instance);

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

        return $defaultBadgeUsageConfig;
    }
}
