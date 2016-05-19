<?php

namespace Icap\BadgeBundle\Listener;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Pager\PagerFactory;
use Claroline\CoreBundle\Rule\Validator;
use Claroline\CoreBundle\Event\DisplayToolEvent;
use Claroline\CoreBundle\Event\LogCreateEvent;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Icap\BadgeBundle\Entity\Badge;
use Icap\BadgeBundle\Manager\BadgeManager;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @DI\Service
 */
class BadgeListener
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * @var \Icap\BadgeBundle\Manager\BadgeManager
     */
    private $badgeManager;

    /**
     * @var \Symfony\Bundle\TwigBundle\TwigEngine
     */
    private $templateingEngine;

    /***
     * @var \Claroline\CoreBundle\Rule\Validator
     */
    private $ruleValidator;

    /**
     * @var \Claroline\CoreBundle\Pager\PagerFactory
     */
    private $pagerFactory;

    /**
     * @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var Registry
     */
    private $doctrine;

    /**
     * @DI\InjectParams({
     *     "entityManager" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "badgeManager" = @DI\Inject("icap_badge.manager.badge"),
     *     "templatingEngine" = @DI\Inject("templating"),
     *     "ruleValidator" = @DI\Inject("claroline.rule.validator"),
     *     "pagerFactory" = @DI\Inject("claroline.pager.pager_factory"),
     *     "tokenStorage" = @DI\Inject("security.token_storage"),
     *     "doctrine" = @DI\Inject("doctrine"),
     * })
     */
    public function __construct(
        EntityManager $entityManager,
        BadgeManager $badgeManager,
        TwigEngine $templatingEngine,
        Validator $ruleValidator,
        PagerFactory $pagerFactory,
        TokenStorageInterface $tokenStorage,
        Registry $doctrine
    ) {
        $this->entityManager = $entityManager;
        $this->badgeManager = $badgeManager;
        $this->templateingEngine = $templatingEngine;
        $this->ruleValidator = $ruleValidator;
        $this->pagerFactory = $pagerFactory;
        $this->tokenStorage = $tokenStorage;
        $this->doctrine = $doctrine;
    }

    /**
     * @DI\Observe("claroline.log.create")
     *
     * @param \Claroline\CoreBundle\Event\LogCreateEvent $event
     */
    public function onLog(LogCreateEvent $event)
    {
        /** @var \Icap\BadgeBundle\Repository\BadgeRuleRepository $badgeRuleRepository */
        $badgeRuleRepository = $this->entityManager->getRepository('IcapBadgeBundle:BadgeRule');
        /** @var \Icap\BadgeBundle\Entity\Badge[] $badges */
        $badges = $badgeRuleRepository->findBadgeAutomaticallyAwardedFromAction($event->getLog());

        if (0 < count($badges)) {
            $doer = $event->getLog()->getDoer();
            $receiver = $event->getLog()->getReceiver();

            foreach ($badges as $badge) {
                $nbRules = count($badge->getRules());

                if (null !== $doer && !$this->userHasBadge($doer, $badge)) {
                    $resources = $this->ruleValidator->validate($badge, $doer);

                    if (0 < $resources['validRules'] && $resources['validRules'] >= $nbRules) {
                        $this->badgeManager->addBadgeToUser($badge, $doer);
                    }
                }

                if (null !== $receiver && !$this->userHasBadge($receiver, $badge)) {
                    $resources = $this->ruleValidator->validate($badge, $receiver);

                    if (0 < $resources['validRules'] && $resources['validRules'] >= $nbRules) {
                        $this->badgeManager->addBadgeToUser($badge, $receiver);
                    }
                }
            }
        }
    }

    /***
     * @param User  $user
     * @param Badge $badge
     *
     * @return bool
     */
    protected function userHasBadge(User $user, Badge $badge)
    {
        $userBadge = $this->entityManager->getRepository('IcapBadgeBundle:UserBadge')->findOneByBadgeAndUser($badge, $user);

        return null !== $userBadge;
    }

    /**
     * @DI\Observe("open_tool_workspace_badges")
     *
     * @param DisplayToolEvent $event
     */
    public function onWorkspaceOpenBadges(DisplayToolEvent $event)
    {
        $event->setContent($this->badgesManagement($event->getWorkspace()));
    }

    /**
     * @DI\Observe("open_tool_workspace_my_badges")
     *
     * @param DisplayToolEvent $event
     */
    public function onWorkspaceOpenMybadges(DisplayToolEvent $event)
    {
        $event->setContent($this->myWorkspaceBadges($event->getWorkspace()));
    }

    /**
     * @DI\Observe("open_tool_desktop_all_my_badges")
     *
     * @param DisplayToolEvent $event
     */
    public function onDesktopToolMenuConfigure(DisplayToolEvent $event)
    {
        $event->setContent($this->myDesktopBadges());
    }

    /**
     * @DI\Observe("list_all_my_badges")
     *
     * @param DisplayToolEvent $event
     *
     * @return string (content)
     */
    public function onListAllMyBadges(DisplayToolEvent $event)
    {
        $userBadges = $this->badgeManager->getLoggedUserBadges();
        $content = $this->templateingEngine->render(
            'IcapBadgeBundle:Profile:myProfileWidgetBadges.html.twig',
            array('userBadges' => $userBadges)
        );

        $event->setContent($content);
        $event->stopPropagation();
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     *
     * @return string
     */
    private function badgesManagement(Workspace $workspace)
    {
        /** @var \Icap\BadgeBundle\Repository\BadgeRepository $badgeRepository */
        $badgeRepository = $this->doctrine->getRepository('IcapBadgeBundle:Badge');

        /** @var \Icap\BadgeBundle\Repository\UserBadgeRepository $userBadgeRepository */
        $userBadgeRepository = $this->doctrine->getRepository('IcapBadgeBundle:UserBadge');

        $totalBadges = $badgeRepository->countByWorkspace($workspace);
        $totalBadgeAwarded = $userBadgeRepository->countAwardedBadgeByWorkspace($workspace);
        $mostAwardedBadges = $userBadgeRepository->findWorkspaceMostAwardedBadges($workspace);
        $countBadgesPerUser = $userBadgeRepository->countBadgesPerUser($workspace);

        $parameters = array(
            'badgePage' => 1,
            'claimPage' => 1,
            'userPage' => 1,
            'workspace' => $workspace,
            'mostAwardedBadges' => $mostAwardedBadges,
            'badges_per_user' => $countBadgesPerUser,
            'add_link' => 'icap_badge_workspace_tool_badges_add',
            'edit_link' => array(
                'url' => 'icap_badge_workspace_tool_badges_edit',
                'suffix' => '#!edit',
            ),
            'delete_link' => 'icap_badge_workspace_tool_badges_delete',
            'view_link' => 'icap_badge_workspace_tool_badges_edit',
            'current_link' => 'icap_badge_workspace_tool_badges',
            'claim_link' => 'icap_badge_workspace_tool_manage_claim',
            'statistics_link' => 'icap_badge_workspace_tool_badges_statistics',
            'csv_link' => 'icap_badge_workspace_export_csv',
            'totalBadges' => $totalBadges,
            'totalAwarding' => $userBadgeRepository->countAwardingByWorkspace($workspace),
            'totalBadgeAwarded' => $totalBadgeAwarded,
            'totalBadgeNotAwarded' => $totalBadges - $totalBadgeAwarded,
            'route_parameters' => array(
                'workspaceId' => $workspace->getId(),
            ),
        );

        return $this->templateingEngine->render(
            'IcapBadgeBundle:Tool:Workspace\list.html.twig',
            array('workspace' => $workspace, 'parameters' => $parameters)
        );
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     *
     * @return string
     */
    private function myWorkspaceBadges(Workspace $workspace)
    {
        $user = $this->tokenStorage->getToken()->getUser();

        return $this->templateingEngine->render(
            'IcapBadgeBundle:Tool:MyWorkspace\toolList.html.twig',
            array(
                'workspace' => $workspace,
                'user' => $user,
            )
        );
    }

    /**
     * @return string
     */
    private function myDesktopBadges()
    {
        $user = $this->tokenStorage->getToken()->getUser();

        $this->doctrine->getManager()->getFilters()->disable('softdeleteable');
        $userBadges = $this->doctrine->getRepository('IcapBadgeBundle:UserBadge')->findByUser($user);
        $badgeClaims = $this->doctrine->getRepository('IcapBadgeBundle:BadgeClaim')->findByUser($user);
        $badgeCollections = $this->doctrine->getRepository('IcapBadgeBundle:BadgeCollection')->findByUser($user);

        return $this->templateingEngine->render(
            'IcapBadgeBundle:Profile:badges.html.twig',
            array(
                'userBadges' => $userBadges,
                'badgeClaims' => $badgeClaims,
                'badgeCollections' => $badgeCollections,
            )
        );
    }
}
