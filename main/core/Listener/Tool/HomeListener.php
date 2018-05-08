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

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\Widget\Widget;
use Claroline\CoreBundle\Event\DisplayToolEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
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

    /** @var SerializerProvider */
    private $serializer;

    /**
     * HomeListener constructor.
     *
     * @DI\InjectParams({
     *     "authorization" = @DI\Inject("security.authorization_checker"),
     *     "templating"    = @DI\Inject("templating"),
     *     "serializer"    = @DI\Inject("claroline.api.serializer")
     * })
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param TwigEngine                    $templating
     * @param SerializerProvider            $serializer
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        TwigEngine $templating,
        SerializerProvider $serializer)
    {
        $this->authorization = $authorization;
        $this->templating = $templating;
        $this->serializer = $serializer;
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
        $content = $this->templating->render(
            'ClarolineCoreBundle:Tool:home.html.twig', [
                'editable' => true,
                'context' => [
                    'type' => Widget::CONTEXT_DESKTOP,
                ],
                'tabs' => [],
                'widgets' => [
                    [
                        'id' => 'id1',
                        'type' => 'resource-list',
                        'name' => 'Choisissez votre module de formation',
                        'parameters' => [
                            'display' => 'tiles',
                            'availableDisplays' => ['tiles'],
                            'filterable' => false,
                            'sortable' => false,
                            'paginated' => false,
                        ],
                    ],
                ],
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

        $content = $this->templating->render(
            'ClarolineCoreBundle:Tool:home.html.twig', [
                'workspace' => $workspace,
                'editable' => $this->authorization->isGranted(['home', 'edit'], $workspace),
                'context' => [
                    'type' => Widget::CONTEXT_WORKSPACE,
                    'data' => $this->serializer->serialize($workspace),
                ],
                'tabs' => [],
                'widgets' => [
                    [
                        'id' => 'id1',
                        'type' => 'resource-list',
                        'name' => 'Choisissez votre module de formation',
                        'parameters' => [
                            'display' => 'tiles',
                            'availableDisplays' => ['tiles'],
                            'filterable' => false,
                            'sortable' => false,
                            'paginated' => false,
                        ],
                    ],
                ],
            ]
        );

        $event->setContent($content);
        $event->stopPropagation();
    }
}
