<?php

namespace Claroline\CoreBundle\Event\CatalogEvents;

final class ResourceEvents
{
    /**
     * @Event("Claroline\CoreBundle\Event\Resource\LoadResourceEvent")
     */
    public const RESOURCE_OPEN = 'resource.load';

    /**
     * @Event("Claroline\CoreBundle\Event\Functional\ResourceEvaluationEvent")
     */
    public const RESOURCE_EVALUATION = 'resource_evaluation';
}
