<?php

namespace Claroline\CoreBundle\Event\CatalogEvents;

final class FunctionalEvents
{
    /**
     * @Event("Claroline\CoreBundle\Event\Functional\AddBadgeEvent")
     */
    public const ADD_BADGE = 'event.funcitonal.add_badge';

    /**
     * @Event("Claroline\CoreBundle\Event\Functional\RemoveBadgeEvent")
     */
    public const REMOVE_BADGE = 'event.funcitonal.remove_badge';

    /**
     * @Event("Claroline\CoreBundle\Event\Functional\ResourceOpenEvent")
     */
    public const RESOURCE_OPEN = 'event.functional.resource_open';

    /**
     * @Event("Claroline\CoreBundle\Event\Functional\ResourceEvaluationEvent")
     */
    public const RESOURCE_EVALUATION = 'event.functional.resource_evaluation';

    /**
     * @Event("Claroline\CoreBundle\Event\Functional\ResourceScoreEvent")
     */
    public const RESOURCE_SCORE = 'event.functional.resource_score';

    /**
     * @Event("Claroline\CoreBundle\Event\Functional\ToolOpenEvent")
     */
    public const TOOL_OPEN = 'event.functional.tool_open';
}
