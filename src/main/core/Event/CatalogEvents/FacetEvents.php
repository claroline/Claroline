<?php

namespace Claroline\CoreBundle\Event\CatalogEvents;

final class FacetEvents
{
    /**
     * @Event("Claroline\CoreBundle\Event\Facet\SetFacetValueEvent")
     */
    public const SET_VALUE = 'field_facet.set_value';

    /**
     * @Event("Claroline\CoreBundle\Event\Facet\GetFacetValueEvent")
     */
    public const GET_VALUE = 'field_facet.get_value';

    public static function getEventName(string $eventType, string $fieldType)
    {
        return $eventType.'.'.$fieldType;
    }
}
