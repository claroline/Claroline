<?php

namespace Claroline\CoreBundle\API;

use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.api.serializer")
 */
class SerializerProvider
{
    /**
     * The list of registered serializers in the platform.
     *
     * @var array
     */
    private $serializers = [];

    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * Injects Serializer service.
     *
     * @DI\InjectParams({
     *      "om" = @DI\Inject("claroline.persistence.object_manager")
     * })
     *
     * @param ObjectManager $om
     */
    public function setObjectManager(ObjectManager $om)
    {
        $this->om = $om;
    }

    /**
     * Registers a new serializer.
     *
     * @param mixed $serializer
     *
     * @throws \Exception
     */
    public function add($serializer)
    {
        if (!method_exists($serializer, 'serialize')) {
            throw new \Exception('The serializer '.get_class($serializer).' must implement the method serialize');
        }

        $this->serializers[] = $serializer;
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
        foreach ($this->serializers as $serializer) {
            if (method_exists($serializer, 'getClass')) {
                // 1. the serializer implements the getClass method, so we just call it
                //    this is the recommended way because it's more efficient than using reflection
                $className = $serializer->getClass();
            } else {
                // 2. else, we try to find the correct serializer by using the type hint of the `serialize` method
                //    this is not always possible, because some serializers can not use type hint (mostly because of an Interface),
                //    so for this case the `getClass` method is required
                $p = new \ReflectionParameter([get_class($serializer), 'serialize'], 0);
                $className = $p->getClass()->getName();
            }

            if ($object instanceof $className || $object === $className) {
                return $serializer;
            }
        }

        throw new \Exception(
            sprintf('No serializer found for class "%s" Maybe you forgot to add the "claroline.serializer" tag to your serializer.', get_class($object))
        );
    }

    /**
     * Serializes an object.
     *
     * @param $object - the object to serialize
     *
     * @return mixed - a json serializable structure
     */
    public function serialize($object, $options = [])
    {
        return $this->get($object)->serialize($object, $options);
    }

    /**
     * Serializes an object.
     *
     * @param $object - the object to serialize
     *
     * @return mixed - a json serializable structure
     */
    public function deserialize($class, $data, $options = [])
    {
        $object = null;

        if (!in_array(Options::NO_FETCH, $options)) {
            $object = $this->om->getObject($data, $class);
        }

        if (!$object) {
            $object = new $class();
        }

        return $this->get($class)->deserialize($data, $object, $options);
    }
}
