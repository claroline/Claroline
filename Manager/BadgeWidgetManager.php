<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Entity\Badge\Badge;
use Claroline\CoreBundle\Entity\Badge\BadgeRule;
use Claroline\CoreBundle\Entity\Badge\UserBadge;
use Claroline\CoreBundle\Entity\Badge\Widget\BadgeUsageConfig;
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
