<?php

namespace HeVinci\CompetencyBundle\Listener\Administration;

use Claroline\CoreBundle\Event\OpenAdministrationToolEvent;
use HeVinci\CompetencyBundle\Manager\CompetencyManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpFoundation\Response;

/**
 * Defines the listening methods for all the core extension
 * points used in this plugin (tools and widgets).
 *
 * @DI\Service()
 */
class CompetenciesListener
{
    /** @var CompetencyManager */
    private $competencyManager;
    /** @var TwigEngine */
    private $templating;

    /**
     * CompetenciesListener constructor.
     *
     * @DI\InjectParams({
     *     "competencyManager" = @DI\Inject("hevinci.competency.competency_manager"),
     *     "templating"        = @DI\Inject("templating")
     * })
     *
     * @param CompetencyManager $competencyManager
     * @param TwigEngine        $templating
     */
    public function __construct(
        CompetencyManager $competencyManager,
        TwigEngine $templating
    ) {
        $this->competencyManager = $competencyManager;
        $this->templating = $templating;
    }

    /**
     * @DI\Observe("administration_tool_competencies")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onDisplayTool(OpenAdministrationToolEvent $event)
    {
        $this->competencyManager->ensureHasScale();

        $content = $this->templating->render('HeVinciCompetencyBundle:administration:competencies.html.twig');

        $event->setResponse(new Response($content));
        $event->stopPropagation();
    }
}
