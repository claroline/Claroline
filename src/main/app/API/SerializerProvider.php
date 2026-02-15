<?php

namespace Claroline\AppBundle\API;

use Claroline\AppBundle\Persistence\ObjectManager;

class SerializerProvider
{
    public function __construct(
        private readonly ObjectManager $om,
        private readonly iterable $serializers
    ) {
    }

    /**
     * Returns the class handled by the serializer (It's public because of tests).
     *
     * @throws \Exception
     */
    public function getSerializerHandledClass(object $serializer): string
    {
        if (method_exists($serializer, 'getClass')) {
            // 1. the serializer implements the getClass method, so we just call it
            //    this is the recommended way because it's more efficient than using reflection
            return $serializer->getClass();
        }
        // 2. else, we try to find the correct serializer by using the type hint of the `serialize` method
        //    this is not always possible, because some serializers can not use type hint (mostly because of an Interface),
        //    so for this case the `getClass` method is required
        $p = new \ReflectionParameter([get_class($serializer), 'serialize'], 0);
        $type = method_exists($p, 'getType') ? $p->getType() : $p->getClass();

        if (!$type) {
            throw new \Exception(get_class($serializer).' is missing type hinting or getClass method');
        }

        return $type->getName();
    }

    /**
     * Gets a registered serializer instance.
     *
     * @throws \Exception
     */
    public function get(mixed $object): object
    {
        // search for the correct serializer
        if (is_string($object)) {
            $meta = $this->om->getClassMetaData($object);

            if ($meta) {
                $object = $meta->name;
            }
        }

        foreach ($this->serializers as $serializer) {
            $className = $this->getSerializerHandledClass($serializer);

            if ($object instanceof $className || $object === $className) {
                return $serializer;
            }
        }

        throw new \Exception(sprintf('No serializer found for class "%s" Maybe you forgot to add the "claroline.serializer" tag to your serializer.', is_string($object) ? $object : get_class($object)));
    }

    /**
     * Check if serializer instance exists.
     *
     * @throws \Exception
     */
    public function has(string|object $object): bool
    {
        // search for the correct serializer
        foreach ($this->serializers as $serializer) {
            $className = $this->getSerializerHandledClass($serializer);

            if ($object instanceof $className || $object === $className) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return the list of serializers.
     */
    public function all(): array
    {
        return $this->serializers instanceof \Traversable ? iterator_to_array($this->serializers) : $this->serializers;
    }

    /**
     * Serializes an object.
     *
     * @param mixed $object  - the object to serialize
     * @param array $options - the serialization options
     *
     * @return array - a json serializable structure
     */
    public function serialize(object $object, ?array $options = []): array
    {
        if (!$object) {
            return $object;
        }

        $data = $this->get($object)->serialize($object, $options);

        // if a serializer wants to return a stdClass, we want an array
        if (is_object($data)) {
            $data = json_decode(json_encode($data), true);
        }

        return $data;
    }

    /**
     * Serializes an object.
     *
     * @param array $data    - the data to deserialize
     * @param array $options - the deserialization options
     *
     * @return object - the resulting entity of deserialization
     */
    public function deserialize(array $data, object $object, ?array $options = []): object
    {
        // search for the correct serializer
        $meta = $this->om->getClassMetaData(get_class($object));

        if ($meta) {
            $class = $meta->name;
        }

        if ($class) {
            $serializer = $this->get($class);
            if (method_exists($serializer, 'deserialize')) {
                $serializer->deserialize($data, $object, $options);
            }
        }

        return $object;
    }
}
