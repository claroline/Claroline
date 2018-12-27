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
     * @param $prop   - the property path
     * @param $setter - the setter to use
     * @param $data   - the data array
     * @param $object - the object to use the setter on
     */
    public function sipe($prop, $setter, $data = [], $object)
    {
        if ($data && is_array($data)) {
            try {
                $value = ArrayUtils::get($data, $prop);

                $object->{$setter}($value);
            } catch (\Exception $e) {
            }
        }
    }

    public function getUuid($object, array $options): string
    {
        return in_array(Options::REFRESH_UUID, $options) ?
            $object->generateUuid() :
            $object->getUuid();
    }
}
