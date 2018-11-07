<?php

namespace Claroline\CoreBundle\Listener\Administration;

use Claroline\CoreBundle\API\Serializer\ParametersSerializer;
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
     *     "serializer" = @DI\Inject("claroline.serializer.parameters")
     * })
     *
     * @param TwigEngine           $templating
     * @param ParametersSerializer $serializer
     */
    public function __construct(
        TwigEngine $templating,
        ParametersSerializer $serializer
    ) {
        $this->templating = $templating;
        $this->serializer = $serializer;
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
            ]
        );

        $event->setResponse(new Response($content));
        $event->stopPropagation();
    }
}
