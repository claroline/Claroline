<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AppBundle\API\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Utils\ArrayUtils;

trait SerializerTrait
{
    /**
     * SetIfPropertyExists.
     * Sets an entity prop from an array data source.
     *
     * @param string $prop   - the property path
     * @param string $setter - the setter to use
     * @param mixed  $data   - the data array
     * @param mixed  $object - the object to use the setter on
     * @param bool   $trim
     */
    public function sipe($prop, $setter, $data, $object, $trim = true)
    {
        if ($data && is_array($data)) {
            if (ArrayUtils::has($data, $prop)) {
                $value = ArrayUtils::get($data, $prop);

                if (is_string($value) && $trim) {
                    $value = trim($value);
                }

                $object->{$setter}($value);
            }
        }
    }

    /**
     * @deprecated. UUIDs must be refreshed in deserialize only.
     */
    public function getUuid($object, array $options): string
    {
        return in_array(Options::REFRESH_UUID, $options) ?
            $object->generateUuid() :
            $object->getUuid();
    }
}
