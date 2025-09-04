<?php

namespace Claroline\AppBundle\API\Finder\Type;

use Claroline\AppBundle\API\Finder\AbstractType;

/**
 * Base application does not provide any implementation for tags.
 * The tag feature is provided through an optional plugin.
 *
 * This is just a placeholder for entities which want to use tags when it is available without creating hard dependency on it.
 */
class TagType extends AbstractType
{
}
