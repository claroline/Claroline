<?php

namespace Claroline\CoreBundle\Event\Facet;

/**
 * An event dispatched when receiving a field facet value from the REST api.
 * This allows subscribers to modify the value before it is stored in the DB.
 */
class SetFacetValueEvent extends AbstractFacetValueEvent
{
}
