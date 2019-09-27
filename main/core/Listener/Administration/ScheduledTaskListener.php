<?php

namespace Claroline\CoreBundle\Listener\Administration;

use Claroline\AppBundle\API\Options;
use Claroline\CoreBundle\API\Serializer\ParametersSerializer;
use Claroline\CoreBundle\Event\OpenAdministrationToolEvent;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Scheduled tasks tool.
 *
 * @DI\Service()
 */
class ScheduledTaskListener
{
    /** @var ParametersSerializer */
    private $parametersSerializer;

    /**
     * ScheduledTaskListener constructor.
     *
     * @DI\InjectParams({
     *     "parametersSerializer" = @DI\Inject("Claroline\CoreBundle\API\Serializer\ParametersSerializer")
     * })
     *
     * @param ParametersSerializer $parametersSerializer
     */
    public function __construct(ParametersSerializer $parametersSerializer)
    {
        $this->parametersSerializer = $parametersSerializer;
    }

    /**
     * Displays scheduled tasks administration tool.
     *
     * @DI\Observe("administration_tool_tasks_scheduling")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onDisplayTool(OpenAdministrationToolEvent $event)
    {
        $parameters = $this->parametersSerializer->serialize([Options::SERIALIZE_MINIMAL]);

        $event->setData([
            'isCronConfigured' => isset($parameters['is_cron_configured']) && $parameters['is_cron_configured'],
        ]);
        $event->stopPropagation();
    }
}
