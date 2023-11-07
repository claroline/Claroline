<?php

namespace Claroline\CoreBundle\Event\CatalogEvents;

final class ResourceEvents
{
    /**
     * @Event("Claroline\CoreBundle\Event\Resource\LoadResourceEvent")
     */
    public const RESOURCE_OPEN = 'resource_load';

    /**
     * @Event("Claroline\CoreBundle\Event\Resource\EmbedResourceEvent")
     */
    public const EMBED = 'resource.embed';
}
