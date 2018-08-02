<?php

namespace Claroline\CoreBundle\Listener\Administration;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\CoreBundle\API\Serializer\ParametersSerializer;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\OpenAdministrationToolEvent;
use Claroline\CoreBundle\Manager\ToolManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpFoundation\Response;

/**
 * Workspace administration tool.
 *
 * @DI\Service()
 */
class WorkspaceListener
{
    /** @var TwigEngine */
    private $templating;

    /** @var ParametersSerializer */
    private $parametersSerializer;

    /** @var ToolManager */
    private $toolManager;

    /** @var FinderProvider */
    private $finder;

    /**
     * WorkspaceListener constructor.
     *
     * @DI\InjectParams({
     *     "parametersSerializer" = @DI\Inject("claroline.serializer.parameters"),
     *     "templating"           = @DI\Inject("templating"),
     *     "toolManager"          = @DI\Inject("claroline.manager.tool_manager"),
     *     "finder"               = @DI\Inject("claroline.api.finder")
     * })
     *
     * @param TwigEngine           $templating
     * @param ParametersSerializer $parametersSerializer,
     * @param ToolManager          $toolManager
     */
    public function __construct(
        TwigEngine $templating,
        ParametersSerializer $parametersSerializer,
        ToolManager $toolManager,
        FinderProvider $finder
    ) {
        $this->templating = $templating;
        $this->parametersSerializer = $parametersSerializer;
        $this->toolManager = $toolManager;
        $this->finder = $finder;
    }

    /**
     * Displays workspace administration tool.
     *
     * @DI\Observe("administration_tool_workspace_management")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onDisplayTool(OpenAdministrationToolEvent $event)
    {
        $workspaceTools = $this->toolManager->getAvailableWorkspaceTools();

        $content = $this->templating->render(
            'ClarolineCoreBundle:administration:workspaces.html.twig',
            [
                'parameters' => $this->parametersSerializer->serialize(),
                'tools' => array_map(function (Tool $tool) {
                    return ['name' => $tool->getName()];
                }, $workspaceTools),
                'models' => $this->finder->search(Workspace::class, ['filters' => ['model' => true]]),
            ]
        );

        $event->setResponse(new Response($content));
        $event->stopPropagation();
    }
}
