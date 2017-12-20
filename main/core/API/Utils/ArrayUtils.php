<?php

namespace Claroline\CoreBundle\API\Utils;

class ArrayUtils
{
    /**
     * This is more or less the equivalent of lodash set for array.
     *
     * @param &$object - the array
     * @param $keys    - the property path
     * @param value    - the property value
     */
    public function set(array &$object, $keys, $value)
    {
        $keys = explode('.', $keys);
        $depth = count($keys);
        $key = array_shift($keys);

        if ($depth === 1) {
            $object[$key] = $value;
        } else {
            if (!isset($object[$key])) {
                $object[$key] = [];
            } elseif (!is_array($object[$key])) {
                throw new \Exception('Cannot set property because it already exists as a non \stdClass');
            }

            $this->set($object[$key], implode('.', $keys), $value);
        }
    }

    /**
     * This is more or less the equivalent of lodash get for array.
     *
     * @param &$object - the array
     * @param $keys    - the property path
     * @param value    - the property value
     */
    public function get($object, $keys)
    {
        $parts = explode('.', $keys);
        $key = array_shift($parts);

        if (isset($object[$key])) {
            if (is_array($object[$key])) {
                return $this->get($object, implode('.', $parts));
            }

            return $object[$key];
        }

        throw new \Exception("Key {$keys} doesn't exist for array keys [".implode(',', array_keys($object)).']');
    }
}
