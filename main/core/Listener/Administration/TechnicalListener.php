<?php

namespace Claroline\CoreBundle\Listener\Administration;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\ParametersSerializer;
use Claroline\CoreBundle\Entity\Tool\AdminTool;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Event\OpenAdministrationToolEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpFoundation\Response;

/**
 * @DI\Service()
 */
class TechnicalListener
{
    /** @var TwigEngine */
    private $templating;

    /** @var ParametersSerializer */
    private $serializer;

    /**
     * AppearanceListener constructor.
     *
     * @DI\InjectParams({
     *     "templating" = @DI\Inject("templating"),
     *     "serializer" = @DI\Inject("claroline.serializer.parameters"),
     *     "om"         = @DI\Inject("claroline.persistence.object_manager")
     * })
     *
     * @param TwigEngine           $templating
     * @param ParametersSerializer $serializer
     */
    public function __construct(
        TwigEngine $templating,
        ParametersSerializer $serializer,
        ObjectManager $om
    ) {
        $this->templating = $templating;
        $this->serializer = $serializer;
        $this->om = $om;
    }

    /**
     * Displays technical administration tool.
     *
     * @DI\Observe("administration_tool_technical_settings")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onDisplayTool(OpenAdministrationToolEvent $event)
    {
        $content = $this->templating->render(
            'ClarolineCoreBundle:administration:technical.html.twig', [
                'context' => [
                    'type' => Tool::ADMINISTRATION,
                ],
                'parameters' => $this->serializer->serialize(),
                'adminTools' => array_map(function (AdminTool $tool) {
                    return $tool->getName();
                }, $this->om->getRepository(AdminTool::class)->findAll()),
            ]
        );

        $event->setResponse(new Response($content));
        $event->stopPropagation();
    }
}
