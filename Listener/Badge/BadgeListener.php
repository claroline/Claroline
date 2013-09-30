<?php

namespace Claroline\CoreBundle\Listener\Badge;

use Claroline\CoreBundle\Badge\BadgeRuleChecker;
use Claroline\CoreBundle\Entity\Log\Log;
use Claroline\CoreBundle\Event\LogCreateEvent;
use Claroline\CoreBundle\Manager\BadgeManager;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service
 */
class BadgeListener
{
    private $entityManager;

    private $badgeManager;

    /**
     * @DI\InjectParams({
     *     "entityManager" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "badgeManager"  = @DI\Inject("claroline.manager.badge"),
     * })
     */
    public function __construct(EntityManager $entityManager, BadgeManager $badgeManager)
    {
        $this->entityManager = $entityManager;
        $this->badgeManager  = $badgeManager;
    }

    /**
     * @DI\Observe("claroline.log.create")
     *
     * @param \Claroline\CoreBundle\Event\LogCreateEvent $event
     */
    public function onLog(LogCreateEvent $event)
    {
        /** @var \Claroline\CoreBundle\Repository\Badge\BadgeRuleRepository $badgeRuleRepository */
        $badgeRuleRepository = $this->entityManager->getRepository('ClarolineCoreBundle:Badge\BadgeRule');
        $badges = $badgeRuleRepository->findBadgeFromAction($event->getLog()->getAction());

        if (0 < count($badges)) {

            $badgeRuleChecker = new BadgeRuleChecker($this->entityManager->getRepository('ClarolineCoreBundle:Log\Log'));
            $user             = $event->getLog()->getDoer();

            foreach ($badges as $badge) {
                if (!$user->hasBadge($badge)) {
                    $resources = $badgeRuleChecker->checkBadge($badge, $user);

                    if ($resources) {
                        $this->badgeManager->addBadgeToUsers($badge, array($user));
                    }
                }
            }
        }
    }
}
