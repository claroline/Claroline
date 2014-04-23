<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Listener\Badge;

use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Pager\PagerFactory;
use Claroline\CoreBundle\Rule\Validator;
use Claroline\CoreBundle\Event\DisplayToolEvent;
use Claroline\CoreBundle\Event\LogCreateEvent;
use Claroline\CoreBundle\Manager\BadgeManager;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Security\Core\SecurityContext;

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
     * @var \Claroline\CoreBundle\Manager\BadgeManager
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
     * @var \Symfony\Component\Security\Core\SecurityContext
     */
    private $securityContext;

    /**
     * @DI\InjectParams({
     *     "entityManager"     = @DI\Inject("doctrine.orm.entity_manager"),
     *     "badgeManager"      = @DI\Inject("claroline.manager.badge"),
     *     "templatingEngine"  = @DI\Inject("templating"),
     *     "ruleValidator"     = @DI\Inject("claroline.rule.validator"),
     *     "pagerFactory"      = @DI\Inject("claroline.pager.pager_factory"),
     *     "securityContext"   = @DI\Inject("security.context")
     * })
     */
    public function __construct(
        EntityManager $entityManager,
        BadgeManager $badgeManager,
        TwigEngine $templatingEngine,
        Validator $ruleValidator,
        PagerFactory $pagerFactory,
        SecurityContext $securityContext
    )
    {
        $this->entityManager     = $entityManager;
        $this->badgeManager      = $badgeManager;
        $this->templateingEngine = $templatingEngine;
        $this->ruleValidator     = $ruleValidator;
        $this->pagerFactory      = $pagerFactory;
        $this->securityContext   = $securityContext;
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
        /** @var \Claroline\CoreBundle\Entity\badge\Badge[] $badges */
        $badges              = $badgeRuleRepository->findBadgeFromAction($event->getLog()->getAction());

        if (0 < count($badges)) {

            $doer     = $event->getLog()->getDoer();
            $receiver = $event->getLog()->getReceiver();

            foreach ($badges as $badge) {
                $nbRules      = count($badge->getRules());

                if (null !== $doer && !$doer->hasBadge($badge)) {
                    $resources    = $this->ruleValidator->validate($badge, $doer);
                    $nbValidRules = count($resources['validRules']);

                    if(0 < $nbValidRules && $nbValidRules >= $nbRules) {
                        $this->badgeManager->addBadgeToUser($badge, $doer);
                    }
                }

                if (null !== $receiver && !$receiver->hasBadge($badge)) {
                    $resources = $this->ruleValidator->validate($badge, $receiver);
                    $nbValidRules = count($resources['validRules']);

                    if(0 < $nbValidRules && $nbValidRules >= $nbRules) {
                        $this->badgeManager->addBadgeToUser($badge, $receiver);
                    }
                }
            }
        }
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
        $event->setContent($this->myBadges($event->getWorkspace()));
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     *
     * @return string
     */
    private function badgesManagement(AbstractWorkspace $workspace)
    {
        $parameters = array(
            'badgePage'    => 1,
            'claimPage'    => 1,
            'workspace'    => $workspace,
            'add_link'     => 'claro_workspace_tool_badges_add',
            'edit_link'    => array(
                'url'    => 'claro_workspace_tool_badges_edit',
                'suffix' => '#!edit'
            ),
            'delete_link'  => 'claro_workspace_tool_badges_delete',
            'view_link'    => 'claro_workspace_tool_badges_edit',
            'current_link' => 'claro_workspace_tool_badges',
            'claim_link'   => 'claro_workspace_tool_manage_claim',
            'claim_link'   => 'claro_workspace_tool_manage_claim',
            'route_parameters' => array(
                'workspaceId' => $workspace->getId()
            ),
        );

        return $this->templateingEngine->render(
            'ClarolineCoreBundle:Badge:Tool\Workspace\list.html.twig',
            array('workspace' => $workspace, 'parameters' => $parameters)
        );
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     *
     * @return string
     */
    private function myBadges(AbstractWorkspace $workspace)
    {
        $user = $this->securityContext->getToken()->getUser();

        return $this->templateingEngine->render(
            'ClarolineCoreBundle:Badge:Tool\MyWorkspace\toolList.html.twig',
            array(
                'workspace' => $workspace,
                'user'      => $user
            )
        );
    }
}
