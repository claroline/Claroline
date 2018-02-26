<?php

namespace Claroline\AppBundle\API;

use Claroline\AppBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use JVal\Context;
use JVal\Registry;
use JVal\Resolver;
use JVal\Uri;
use JVal\Utils;
use JVal\Walker;

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
    /** @var ObjectManager */
    private $om;
    /** @var string */
    private $rootDir;
    /** @var string */
    private $baseUri;

    /**
     * Injects Serializer service.
     *
     * @DI\InjectParams({
     *      "om"      = @DI\Inject("claroline.persistence.object_manager"),
     *      "rootDir" = @DI\Inject("%kernel.root_dir%")
     * })
     *
     * @param ObjectManager $om
     * @param string        $rootDir
     */
    public function setObjectManager(ObjectManager $om, $rootDir)
    {
        $this->om = $om;
        $this->rootDir = $rootDir.'/..';
        $this->baseUri = 'https://github.com/claroline/Distribution/tree/master';
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
     * Returns the class handled by the serializer.
     *
     * @param mixed $serializer
     *
     * @return $string
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
        foreach ($this->serializers as $serializer) {
            $className = $this->getSerializerHandledClass($serializer);

            if ($object instanceof $className || $object === $className) {
                return $serializer;
            }
        }

        $className = is_object($object) ? get_class($object) : $object;

        throw new \Exception(
            sprintf('No serializer found for class "%s" Maybe you forgot to add the "claroline.serializer" tag to your serializer.', $className)
        );
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
        return $this->get($object)->serialize($object, $options);
    }

    /**
     * Serializes an object.
     *
     * @param string $class   - the class of the object to deserialize
     * @param mixed  $data    - the data to deserialize
     * @param array  $options - the deserialization options
     *
     * @return mixed - the resulting entity of deserialization
     */
    public function deserialize($class, $data, $options = [])
    {
        $object = null;
        $serializer = $this->get($class);

        if (!in_array(Options::NO_FETCH, $options)) {
            //first find by uuid and id
            $object = $this->om->getObject($data, $class);

            //maybe move that chunk of code somewhere else
            //or remove it as it doens't do anyhting anymore I think
            if (!$object) {
                foreach (array_keys($data) as $property) {
                    if (in_array($property, $this->getIdentifiers($class)) && !$object) {
                        $object = $this->om->getRepository($class)->findOneBy([$property => $data[$property]]);
                    }
                }
            }
        }

        if (!$object) {
            $object = new $class();
        }

        $serializer->deserialize($data, $object, $options);

        return $object;
    }

    /**
     * Get the identifier list from the json schema.
     */
    public function getIdentifiers($class)
    {
        $schema = $this->getSchema($class);

        if (isset($schema->claroline)) {
            return $schema->claroline->ids;
        }

        return [];
    }

    /**
     * Gets the json schema of a class.
     *
     * @param string $class
     *
     * @return \stdClass
     */
    public function getSchema($class)
    {
        $serializer = $this->get($class);

        if (method_exists($serializer, 'getSchema')) {
            $url = $serializer->getSchema();
            $path = explode('/', $url);
            $absolutePath = $this->rootDir.'/vendor/claroline/distribution/'
            .$path[1].'/'.$path[2].'/Resources/schemas/'.$path[3];

            return $this->loadSchema($absolutePath);
        }
    }

    /**
     * Loads a json schema.
     *
     * @param string $path
     *
     * @return \stdClass
     */
    public function loadSchema($path)
    {
        $schema = Utils::LoadJsonFromFile($path);

        $hook = function ($uri) {
            return $this->resolveRef($uri);
        };

        //this is the resolution of the $ref thingy with Jval classes
        //resolver can take a Closure parameter to change the $ref value
        $resolver = new Resolver();
        $resolver->setPreFetchHook($hook);
        $walker = new Walker(new Registry(), $resolver);
        $schema = $walker->resolveReferences($schema, new Uri(''));

        return $walker->parseSchema($schema, new Context());
    }

    /**
     * @param string $class
     *
     * @return string
     */
    public function getSampleDirectory($class)
    {
        $serializer = $this->get($class);

        if (method_exists($serializer, 'getSamples')) {
            $url = $serializer->getSamples();
            $path = explode('/', $url);

            return $this->rootDir.'/vendor/claroline/distribution/'
              .$path[1].'/'.$path[2].'/Resources/samples/'.$path[3];
        }
    }

    /**
     * Checks if a class has a schema defined.
     *
     * @param string $class
     *
     * @return bool
     */
    public function hasSchema($class)
    {
        return method_exists($this->get($class), 'getSchema');
    }

    /**
     * Converts distant schema URI to a local one to load schemas from source code.
     *
     * @param string $uri
     *
     * @return string mixed
     */
    private function resolveRef($uri)
    {
        $uri = str_replace($this->baseUri, '', $uri);
        $schemaDir = realpath("{$this->rootDir}/vendor/claroline/distribution");

        return $schemaDir.'/'.$uri;
    }
}
