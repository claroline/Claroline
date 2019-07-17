<?php

namespace HeVinci\CompetencyBundle\Listener\Administration;

use Claroline\CoreBundle\Event\OpenAdministrationToolEvent;
use HeVinci\CompetencyBundle\Manager\CompetencyManager;
use JMS\DiExtraBundle\Annotation as DI;

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

    /**
     * CompetenciesListener constructor.
     *
     * @DI\InjectParams({
     *     "competencyManager" = @DI\Inject("hevinci.competency.competency_manager")
     * })
     *
     * @param CompetencyManager $competencyManager
     */
    public function __construct(CompetencyManager $competencyManager)
    {
        $this->competencyManager = $competencyManager;
    }

    /**
     * @DI\Observe("administration_tool_competencies")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onDisplayTool(OpenAdministrationToolEvent $event)
    {
        $this->competencyManager->ensureHasScale();
        $event->setData([]);
        $event->stopPropagation();
    }
}
