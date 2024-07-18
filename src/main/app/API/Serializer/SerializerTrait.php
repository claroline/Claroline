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
    public function sipe(string $prop, string $setter, array $data, mixed $object, bool $trim = true): void
    {
        if (!empty($data)) {
            if (ArrayUtils::has($data, $prop)) {
                $value = ArrayUtils::get($data, $prop);

                if (is_string($value) && $trim) {
                    $value = trim($value);
                }

                $object->{$setter}($value);
            }
        }
    }
}
