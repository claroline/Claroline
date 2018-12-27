<?php

namespace Claroline\AppBundle\API\Utils;

class ArrayUtils
{
    /**
     * This is more or less the equivalent of lodash set for array.
     *
     * @param array  $object
     * @param string $keys   - the property path
     * @param $value
     *
     * @throws \Exception
     */
    public static function set(array &$object, $keys, $value)
    {
        $keys = explode('.', $keys);
        $depth = count($keys);
        $key = array_shift($keys);

        if (1 === $depth) {
            $object[$key] = $value;
        } else {
            if (!isset($object[$key])) {
                $object[$key] = [];
            } elseif (!is_array($object[$key])) {
                throw new \Exception('Cannot set property because it already exists as a non \stdClass');
            }

            static::set($object[$key], implode('.', $keys), $value);
        }
    }

    /**
     * This is more or less the equivalent of lodash get for array.
     *
     * @param array  $object - the array
     * @param string $keys   - the property path
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public static function get(array $object, $keys)
    {
        $parts = explode('.', $keys);
        $key = array_shift($parts);

        if (isset($object[$key])) {
            if (!empty($parts) && is_array($object[$key])) {
                return static::get($object[$key], implode('.', $parts));
            }

            return $object[$key];
        }

        if (array_key_exists($key, $object)) {
            return null;
        }
        throw new \Exception("Key `{$keys}` doesn't exist for array keys [".implode(',', array_keys($object)).']');
    }

    /**
     * @param array  $object - the array
     * @param string $keys   - the property path
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public static function has(array $object, $keys)
    {
        try {
            static::get($object, $keys);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
