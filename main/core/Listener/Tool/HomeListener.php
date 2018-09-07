<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Listener\Tool;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\Widget\Widget;
use Claroline\CoreBundle\Event\DisplayToolEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Home tool.
 *
 * @DI\Service()
 */
class HomeListener
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;

    /** @var TwigEngine */
    private $templating;

    /** @var FinderProvider */
    private $finder;

    /** @var SerializerProvider */
    private $serializer;

    /**
     * HomeListener constructor.
     *
     * @DI\InjectParams({
     *     "authorization" = @DI\Inject("security.authorization_checker"),
     *     "tokenStorage"  = @DI\Inject("security.token_storage"),
     *     "templating"    = @DI\Inject("templating"),
     *     "finder"        = @DI\Inject("claroline.api.finder"),
     *     "serializer"    = @DI\Inject("claroline.api.serializer")
     * })
     *
     * @param TokenStorageInterface         $tokenStorage
     * @param TwigEngine                    $templating
     * @param FinderProvider                $finder
     * @param SerializerProvider            $serializer
     * @param AuthorizationCheckerInterface $authorization
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        TwigEngine $templating,
        FinderProvider $finder,
        SerializerProvider $serializer,
        AuthorizationCheckerInterface $authorization
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->templating = $templating;
        $this->finder = $finder;
        $this->serializer = $serializer;
        $this->authorization = $authorization;
    }

    /**
     * Displays home on Desktop.
     *
     * @DI\Observe("open_tool_desktop_home")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayDesktop(DisplayToolEvent $event)
    {
        $currentUser = $this->tokenStorage->getToken()->getUser();

        $tabs = $this->finder->search(
          'Claroline\CoreBundle\Entity\Tab\HomeTab',
          ['filters' => ['user' => $currentUser->getUuid()]]
        );
        $orderedTabs = $tabs['data'];

        foreach ($orderedTabs as $position => $tab) {
            $orderedTabs[$position + 1] = $tab;
            $orderedTabs[$position + 1]['position'] = $position + 1;
        }

        $content = $this->templating->render(
            'ClarolineCoreBundle:tool:home.html.twig', [
                'editable' => true,
                'context' => [
                    'type' => Widget::CONTEXT_DESKTOP,
                ],
                'tabs' => $tabs['data'],
            ]
        );

        $event->setContent($content);
        $event->stopPropagation();
    }

    /**
     * Displays home on Workspace.
     *
     * @DI\Observe("open_tool_workspace_home")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayWorkspace(DisplayToolEvent $event)
    {
        $workspace = $event->getWorkspace();
        $tabs = $this->finder->search(
          'Claroline\CoreBundle\Entity\Tab\HomeTab',
          ['filters' => ['workspace' => $workspace->getUuid()]]
        );

        $orderedTabs = $tabs['data'];

        foreach ($orderedTabs as $position => $tab) {
            $orderedTabs[$position + 1] = $tab;
            $orderedTabs[$position + 1]['position'] = $position + 1;
        }

        $content = $this->templating->render(
            'ClarolineCoreBundle:tool:home.html.twig', [
                'workspace' => $workspace,
                'editable' => $this->authorization->isGranted(['home', 'edit'], $workspace),
                'context' => [
                    'type' => Widget::CONTEXT_WORKSPACE,
                    'data' => $this->serializer->serialize($workspace),
                ],
                'tabs' => $orderedTabs,
            ]
        );

        $event->setContent($content);
        $event->stopPropagation();
    }
}
