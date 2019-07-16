<?php

namespace Claroline\OpenBadgeBundle\Listener;

use Claroline\CoreBundle\API\Serializer\ParametersSerializer;
use Claroline\CoreBundle\Event\OpenAdministrationToolEvent;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service()
 */
class AdministrationListener
{
    /**
     * @DI\InjectParams({
     *     "parameters" = @DI\Inject("claroline.serializer.parameters")
     * })
     */
    public function __construct(
        ParametersSerializer $parameters
    ) {
        $this->parameters = $parameters;
    }

    /**
     * @DI\Observe("administration_tool_open-badge")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onDisplayTool(OpenAdministrationToolEvent $event)
    {
        $event->setData(['parameters' => $this->parameters->serialize()]);
        $event->stopPropagation();
    }
}
