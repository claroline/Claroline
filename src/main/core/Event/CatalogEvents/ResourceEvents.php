<?php

namespace Claroline\CoreBundle\Event\CatalogEvents;

final class ResourceEvents
{
    /**
     * @Event("Claroline\CoreBundle\Event\Resource\LoadResourceEvent")
     */
    public const OPEN = 'resource.load';

    /**
     * @Event("Claroline\CoreBundle\Event\Resource\EmbedResourceEvent")
     */
    public const EMBED = 'resource.embed';

    /**
     * @Event("Claroline\CoreBundle\Event\Resource\DownloadResourceEvent")
     */
    public const DOWNLOAD = 'resource.download';

    /**
     * @Event("Claroline\CoreBundle\Event\Resource\CreateResourceEvent")
     */
    public const CREATE = 'resource.create';

    /**
     * @Event("Claroline\CoreBundle\Event\Resource\UpdateResourceEvent")
     */
    public const UPDATE = 'resource.update';

    /**
     * @Event("Claroline\CoreBundle\Event\Resource\DeleteResourceEvent")
     */
    public const DELETE = 'resource.delete';

    /**
     * @Event("Claroline\CoreBundle\Event\Resource\CopyResourceEvent")
     */
    public const COPY = 'resource.copy';

    /**
     * @Event("Claroline\CoreBundle\Event\Tool\ExportResourceEvent")
     */
    public const EXPORT = 'resource.export';

    /**
     * @Event("Claroline\CoreBundle\Event\Tool\ImportResourceEvent")
     */
    public const IMPORT = 'resource.import';

    public static function getEventName(string $event, string $resourceName = null): string
    {
        if ($resourceName) {
            return $event.'.'.$resourceName;
        }

        return $event;
    }
}
