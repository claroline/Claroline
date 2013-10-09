<?php

namespace Claroline\CoreBundle\Listener\Badge;

use Claroline\CoreBundle\Badge\BadgeRuleChecker;
use Claroline\CoreBundle\Entity\Log\Log;
use Claroline\CoreBundle\Event\DisplayToolEvent;
use Claroline\CoreBundle\Event\LogCreateEvent;
use Claroline\CoreBundle\Manager\BadgeManager;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;

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

    /**
     * @DI\InjectParams({
     *     "entityManager"     = @DI\Inject("doctrine.orm.entity_manager"),
     *     "badgeManager"      = @DI\Inject("claroline.manager.badge"),
     *     "templatingEngine"  = @DI\Inject("templating")
     * })
     */
    public function __construct(EntityManager $entityManager, BadgeManager $badgeManager, TwigEngine $templatingEngine)
    {
        $this->entityManager     = $entityManager;
        $this->badgeManager      = $badgeManager;
        $this->templateingEngine = $templatingEngine;
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

    /**
     * @DI\Observe("open_tool_workspace_badges")
     *
     * @param DisplayToolEvent $event
     */
    public function onWorkspaceOpen(DisplayToolEvent $event)
    {
        $event->setContent($this->workspace($event->getWorkspace()->getId()));
    }

    private function workspace($workspaceId)
    {
        $workspace = $this->entityManager->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);

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

        return $this->templateingEngine->render('ClarolineCoreBundle:Badge:Tool\Workspace\list.html.twig', array('workspace' => $workspace, 'parameters' => $parameters));
    }
}
