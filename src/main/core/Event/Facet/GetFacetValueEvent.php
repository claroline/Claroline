<?php

namespace Claroline\CoreBundle\Event\Facet;

/**
 * An event dispatched when trying to get a field facet value for the REST api.
 * This allows subscribers to modify the value before it is returned to the api.
 */
class GetFacetValueEvent extends AbstractFacetValueEvent
{
}
