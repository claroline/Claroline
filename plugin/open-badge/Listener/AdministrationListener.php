<?php

namespace Claroline\OpenBadgeBundle\Listener;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\CoreBundle\API\Serializer\ParametersSerializer;
use Claroline\CoreBundle\Event\OpenAdministrationToolEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpFoundation\Response;

/**
 * @DI\Service()
 */
class AdministrationListener
{
    /** @var TwigEngine */
    private $templating;

    /** @var FinderProvider */
    private $finder;

    /**
     * AnalyticsListener constructor.
     *
     * @DI\InjectParams({
     *     "templating" = @DI\Inject("templating"),
     *     "finder"     = @DI\Inject("claroline.api.finder"),
     *     "parameters" = @DI\Inject("claroline.serializer.parameters")
     * })
     *
     * @param TwigEngine     $templating
     * @param FinderProvider $finder
     */
    public function __construct(
        TwigEngine $templating,
        FinderProvider $finder,
        ParametersSerializer $parameters
    ) {
        $this->templating = $templating;
        $this->finder = $finder;
        $this->parameters = $parameters;
    }

    /**
     * Displays analytics administration tool.
     *
     * @DI\Observe("administration_tool_open-badge")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onDisplayTool(OpenAdministrationToolEvent $event)
    {
        $content = $this->templating->render('ClarolineOpenBadgeBundle::administration.html.twig', [
          'parameters' => $this->parameters->serialize(),
        ]);
        $event->setResponse(new Response($content));
        $event->stopPropagation();
    }
}
