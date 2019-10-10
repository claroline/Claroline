<?php

namespace HeVinci\CompetencyBundle\Listener\Administration;

use Claroline\CoreBundle\Event\OpenAdministrationToolEvent;
use HeVinci\CompetencyBundle\Manager\CompetencyManager;

/**
 * Defines the listening methods for all the core extension
 * points used in this plugin (tools and widgets).
 */
class CompetenciesListener
{
    /** @var CompetencyManager */
    private $competencyManager;

    /**
     * CompetenciesListener constructor.
     *
     * @param CompetencyManager $competencyManager
     */
    public function __construct(CompetencyManager $competencyManager)
    {
        $this->competencyManager = $competencyManager;
    }

    /**
     * @param OpenAdministrationToolEvent $event
     */
    public function onDisplayTool(OpenAdministrationToolEvent $event)
    {
        $this->competencyManager->ensureHasScale();
        $event->setData([]);
        $event->stopPropagation();
    }
}
