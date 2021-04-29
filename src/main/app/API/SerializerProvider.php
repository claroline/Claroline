<?php

namespace Claroline\AppBundle\API;

use Claroline\AppBundle\Persistence\ObjectManager;

class SerializerProvider
{
    /**
     * The list of registered serializers in the platform.
     *
     * @var iterable
     */
    private $serializers;
    /** @var ObjectManager */
    private $om;
    /** @var string */
    private $rootDir;
    /** @var string */
    private $baseUri;

    /**
     * Injects Serializer service.
     */
    public function __construct(ObjectManager $om, iterable $serializers, string $rootDir)
    {
        $this->om = $om;
        $this->serializers = $serializers;
        $this->rootDir = $rootDir;
        $this->baseUri = 'https://github.com/claroline/Distribution/tree/master';
    }

    /**
     * Returns the class handled by the serializer.
     *
     * @param mixed $serializer
     *
     * @return string
     *
     * @throws \Exception
     */
    public function getSerializerHandledClass($serializer)
    {
        if (method_exists($serializer, 'getClass')) {
            // 1. the serializer implements the getClass method, so we just call it
            //    this is the recommended way because it's more efficient than using reflection
            return $serializer->getClass();
        } else {
            // 2. else, we try to find the correct serializer by using the type hint of the `serialize` method
            //    this is not always possible, because some serializers can not use type hint (mostly because of an Interface),
            //    so for this case the `getClass` method is required
            $p = new \ReflectionParameter([get_class($serializer), 'serialize'], 0);

            if (!$p->getClass()) {
                throw new \Exception(get_class($serializer).' is missing type hinting or getClass method');
            }

            return $p->getClass()->getName();
        }
    }

    /**
     * Gets a registered serializer instance.
     *
     * @param mixed $object
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function get($object)
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
     * @param mixed $object
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function has($object)
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
     *
     * @return mixed[];
     */
    public function all()
    {
        return $this->serializers;
    }

    /**
     * Serializes an object.
     *
     * @param mixed $object  - the object to serialize
     * @param array $options - the serialization options
     *
     * @return mixed - a json serializable structure
     */
    public function serialize($object, $options = [])
    {
        if (!$object) {
            return $object;
        }

        $data = $this->get($object)->serialize($object, $options);

        //if a serializer wants to return a stdClass, we want an array
        if (is_object($data)) {
            $data = json_decode(json_encode($data), true);
        }

        return $data;
    }

    /**
     * Serializes an object.
     *
     * @param mixed $data    - the data to deserialize
     * @param mixed $object
     * @param array $options - the deserialization options
     *
     * @return mixed - the resulting entity of deserialization
     */
    public function deserialize($data, $object, $options = [])
    {
        // search for the correct serializer
        $meta = $this->om->getClassMetaData(get_class($object));

        if ($meta) {
            $class = $meta->name;
        }

        if ($class ?? false) {
            $serializer = $this->get($class);
            if (method_exists($serializer, 'deserialize')) {
                $serializer->deserialize($data, $object, $options);
            }
        }

        return $object;
    }
}
