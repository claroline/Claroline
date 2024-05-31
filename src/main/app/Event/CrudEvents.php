<?php

namespace Claroline\AppBundle\Event;

final class CrudEvents
{
    /**
     * @Event("Claroline\AppBundle\Event\Crud\CreateEvent")
     */
    public const PRE_CREATE = 'crud.pre_create';

    /**
     * @Event("Claroline\AppBundle\Event\Crud\CreateEvent")
     */
    public const POST_CREATE = 'crud.post_create';

    /**
     * @Event("Claroline\AppBundle\Event\Crud\UpdateEvent")
     */
    public const PRE_UPDATE = 'crud.pre_update';

    /**
     * @Event("Claroline\AppBundle\Event\Crud\UpdateEvent")
     */
    public const POST_UPDATE = 'crud.post_update';

    /**
     * @Event("Claroline\AppBundle\Event\Crud\PatchEvent")
     */
    public const PRE_PATCH = 'crud.pre_patch';

    /**
     * @Event("Claroline\AppBundle\Event\Crud\PatchEvent")
     */
    public const POST_PATCH = 'crud.post_patch';

    /**
     * @Event("Claroline\AppBundle\Event\Crud\CopyEvent")
     */
    public const PRE_COPY = 'crud.pre_copy';

    /**
     * @Event("Claroline\AppBundle\Event\Crud\CopyEvent")
     */
    public const POST_COPY = 'crud.post_copy';

    /**
     * @Event("Claroline\AppBundle\Event\Crud\DeleteEvent")
     */
    public const PRE_DELETE = 'crud.pre_delete';

    /**
     * @Event("Claroline\AppBundle\Event\Crud\UpdateEvent")
     */
    public const POST_DELETE = 'crud.post_delete';

    public static function getEventName(string $event, ?string $className = null): string
    {
        if (empty($className)) {
            return $event;
        }

        return $event.'.'.strtolower(str_replace('\\', '_', $className));
    }
}
