<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 1/16/17
 */

namespace Claroline\CoreBundle\Library\Utilities;

/**
 * Base Enum class.
 *
 * Create an enum by implementing this class and adding class constants.
 */
abstract class Enum
{
    /**
     * Enum value.
     *
     * @var mixed
     */
    protected $value;

    /**
     * Store existing constants in a static cache per object.
     *
     * @var array
     */
    protected static $cache = [];

    /**
     * Creates a new value of some type.
     *
     * @param mixed $value
     *
     * @throws \UnexpectedValueException if incompatible type is given
     */
    public function __construct($value)
    {
        if (!$this->isValid($value)) {
            throw new \UnexpectedValueException("Value '$value' is not part of the enum ".get_called_class());
        }

        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Tries to set the value  of this enum.
     *
     * @param string $value
     *
     * @throws Exception If value is not part of this enum
     */
    public function setValue($value)
    {
        if (!$this->isValid($value)) {
            throw new \UnexpectedValueException("Value '$value' is not part of the enum ".get_called_class());
        }
        $this->value = $value;
    }

    /**
     * Returns the enum key (i.e. the constant name).
     *
     * @return mixed
     */
    public function getKey()
    {
        return static::search($this->value);
    }

    public function __get($property)
    {
        return $this->getValue();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->value;
    }

    /**
     * Compares one Enum with another.
     *
     * This method is final, for more information read https://github.com/myclabs/php-enum/issues/4
     *
     * @param Enum $enum
     *
     * @return bool True if Enums are equal, false if not equal
     */
    final public function equals(Enum $enum)
    {
        return $this->getValue() === $enum->getValue();
    }

    /**
     * Returns the names (keys) of all constants in the Enum class.
     *
     * @return array
     */
    public static function keys()
    {
        return array_keys(static::toArray());
    }

    /**
     * Returns instances of the Enum class of all Enum constants.
     *
     * @return static[] Constant name in key, Enum instance in value
     */
    public static function values()
    {
        $values = [];

        foreach (static::toArray() as $key => $value) {
            $values[$key] = new static($value);
        }

        return $values;
    }

    /**
     * Returns all possible values as an array.
     *
     * @return array Constant name in key, constant value in value
     */
    public static function toArray()
    {
        $class = get_called_class();
        if (!array_key_exists($class, static::$cache)) {
            $reflection = new \ReflectionClass($class);
            static::$cache[$class] = $reflection->getConstants();
        }

        return static::$cache[$class];
    }

    /**
     * Check if is valid enum value.
     *
     * @param $value
     *
     * @return bool
     */
    public static function isValid($value)
    {
        return in_array($value, static::toArray(), true);
    }

    /**
     * Check if is valid enum key.
     *
     * @param $key
     *
     * @return bool
     */
    public static function isValidKey($key)
    {
        $array = static::toArray();

        return isset($array[$key]);
    }

    /**
     * Return key for value.
     *
     * @param $value
     *
     * @return mixed
     */
    public static function search($value)
    {
        return array_search($value, static::toArray(), true);
    }

    /**
     * Returns a value when called statically like so: MyEnum::SOME_VALUE() given SOME_VALUE is a class constant.
     *
     * @param string $name
     * @param array  $arguments
     *
     * @return static
     *
     * @throws \BadMethodCallException
     */
    public static function __callStatic($name, $arguments)
    {
        $array = static::toArray();
        if (isset($array[$name])) {
            return new static($array[$name]);
        }

        throw new \BadMethodCallException("No static method or enum constant '$name' in class ".get_called_class());
    }
}
