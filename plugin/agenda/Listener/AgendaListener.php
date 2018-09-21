<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AgendaBundle\Listener;

use Claroline\AgendaBundle\Manager\AgendaManager;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\DisplayToolEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 *  @DI\Service()
 */
class AgendaListener
{
    private $templating;
    private $tokenStorage;
    private $authorization;
    private $container;
    private $request;
    private $agendaManager;

    /**
     * @DI\InjectParams({
     *     "templating"     = @DI\Inject("templating"),
     *     "authorization"  = @DI\Inject("security.authorization_checker"),
     *     "tokenStorage"   = @DI\Inject("security.token_storage"),
     *     "container"      = @DI\Inject("service_container"),
     *     "requestStack"   = @DI\Inject("request_stack"),
     *     "agendaManager"  = @DI\Inject("claroline.manager.agenda_manager")
     * })
     */
    public function __construct(
        TwigEngine $templating,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorization,
        ContainerInterface $container,
        RequestStack $requestStack,
        AgendaManager $agendaManager
    ) {
        $this->templating = $templating;
        $this->tokenStorage = $tokenStorage;
        $this->authorization = $authorization;
        $this->container = $container;
        $this->request = $requestStack->getCurrentRequest();
        $this->agendaManager = $agendaManager;
    }

    /**
     * @DI\Observe("open_tool_workspace_agenda_")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayWorkspaceAgenda(DisplayToolEvent $event)
    {
        $workspace = $event->getWorkspace();
        $editableWorkspace = $this->authorization->isGranted(['agenda_', 'edit'], $workspace);

        $content = $this->templating->render(
            'ClarolineAgendaBundle:tool:agenda.html.twig',
            [
                'workspace' => $workspace,
                'editableWorkspaces' => [$workspace->getUuid() => $editableWorkspace],
            ]
        );

        $event->setContent($content);
    }

    /**
     * @DI\Observe("open_tool_desktop_agenda_")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayDesktopAgenda(DisplayToolEvent $event)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $usr = $this->tokenStorage->getToken()->getUser();
        $listEventsDesktop = $em->getRepository('ClarolineAgendaBundle:Event')->findDesktop($usr, true);
        $listEvents = $em->getRepository('ClarolineAgendaBundle:Event')->findByUser($usr, false);
        $filters = [];
        $editableWorkspaces = [0 => true];

        foreach ($listEvents as $event) {
            $workspaceId = $event->getWorkspace()->getUuid();
            $filters[$workspaceId] = $event->getWorkspace()->getName();
            $editableWorkspaces[$workspaceId] = $this->authorization->isGranted(
              ['agenda_', 'edit'],
              $event->getWorkspace()
          );
        }

        if (count($listEventsDesktop) > 0) {
            $filters[0] = $this->container->get('translator')->trans('desktop', [], 'platform');
        }

        $content = $this->templating->render(
            'ClarolineAgendaBundle:tool:agenda.html.twig',
            [
                'filters' => $filters,
                'editableWorkspaces' => $editableWorkspaces,
            ]
        );

        $event->setContent($content);
    }
}
