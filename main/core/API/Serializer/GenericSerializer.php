<?php

namespace Claroline\CoreBundle\API\Serializer;

use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\ManyToOne;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.generic_serializer")
 */
class GenericSerializer
{
    const INCLUDE_MANY_TO_ONE = 'many_to_one';
    //maybe later include many to many

    /**
     * @DI\Inject("claroline.persistence.object_manager")
     */
    public $om;

    /**
     * @DI\Inject("annotation_reader")
     *
     * @var Reader
     */
    public $reader;

    /**
     * Default serialize method.
     */
    public function serialize($object, array $options = [])
    {
        return $this->mapEntityToObject(
          $this->getSerializableProperties($object),
          $object,
          new \StdClass()
        );
    }

    /**
     * Default deserialize method.
     */
    public function deserialize($data, $object, array $options = [])
    {
        $properties = $this->getSerializableProperties($object, [self::INCLUDE_MANY_TO_ONE]);

        return $this->mapObjectToEntity(
            $properties,
            $this->resolveData($properties, $data, get_class($object)),
            $object
        );
    }

    private function getSerializableProperties($class, $options = [])
    {
        $refClass = new \ReflectionClass($class);
        $dontBeDumbAndShowThis = ['password', 'salt'];
        $seralizableProperties = [];

        foreach ($refClass->getProperties() as $property) {
            foreach ($this->reader->getPropertyAnnotations($property) as $annotation) {
                if ($annotation instanceof Column && !in_array($property->getName(), $dontBeDumbAndShowThis)) {
                    $seralizableProperties[$property->getName()] = $property->getName();
                }
                if (in_array(self::INCLUDE_MANY_TO_ONE, $options) && $annotation instanceof ManyToOne) {
                    $seralizableProperties[$property->getName()] = $property->getName();
                }
            }
        }

        return $seralizableProperties;
    }

    protected function resolveData($mapping, \stdClass $data, $class)
    {
        $resolved = new \stdClass();
        $refClass = new \ReflectionClass($class);

        foreach ($mapping as $dataProperty => $map) {
            foreach ($refClass->getProperties() as $property) {
                if ($property->getName() === $dataProperty && property_exists($data, $dataProperty)) {
                    $resolved->{$dataProperty} = $data->{$map};

                    foreach ($this->reader->getPropertyAnnotations($property) as $annotation) {
                        if ($annotation instanceof ManyToOne) {
                            //basic search by fields here... later create the object aswell
                            if (get_class($data->{$map}) !== $annotation->targetEntity) {
                                try {
                                    $resolved->{$dataProperty} = $this->om
                                        ->getRepository($annotation->targetEntity)
                                        ->findOneBy($this->toArray($data->{$map}));
                                } catch (\Exception $e) {
                                    $resolved->{$dataProperty} = null;
                                }
                            }
                        }
                    }
                }
            }
        }

        return $resolved;
    }

    /**
     * Maps raw object data into entity properties.
     *
     * Mapping array format :
     *  - key   : the name of the property in the \stdClass
     *  - value : either an entity property (accessible with a setter) or a callback
     *
     * @param array     $mapping
     * @param \stdClass $data
     * @param mixed     $entity
     *
     * @return mixed the updated entity
     *
     * @throws \LogicException
     */
    protected function mapObjectToEntity(array $mapping, \stdClass $data, $entity)
    {
        foreach ($mapping as $dataProperty => $map) {
            if (property_exists($data, $dataProperty)) {
                if (is_string($map) || is_object($map) || is_int($map)) {
                    try {
                        $setter = $this->getEntitySetter($entity, $map);
                        if ($data->{$dataProperty}) {
                            // Inject data into entity
                            call_user_func([$entity, $setter], $data->{$dataProperty});
                        }
                    } catch (\LogicException $e) {
                        //no stter
                    }
                } else {
                    // Retrieve the entity setter

                    call_user_func($map, $entity, $data);
                }
            }
        }

        return $entity;
    }

    /**
     * Maps entity properties into raw object.
     *
     * Mapping array format :
     *  - key   : the name of the property to create in the \stdClass
     *  - value : either an entity property (accessible with a getter) or a callback
     *
     * @param array     $mapping
     * @param mixed     $entity
     * @param \stdClass $data
     *
     * @return \stdClass the updated raw object
     */
    protected function mapEntityToObject(array $mapping, $entity, \stdClass $data)
    {
        foreach ($mapping as $dataProperty => $map) {
            $value = null;
            if (is_string($map)) {
                // Retrieve the entity getter
                $getter = $this->getEntityGetter($entity, $map);

                // Inject data into object
                $value = call_user_func([$entity, $getter]);
            } elseif (is_callable($map)) {
                // Call the defined function
                $value = call_user_func($map, $entity);
            }

            $data->{$dataProperty} = $value;
        }

        return $data;
    }

    /**
     * Gets the correct getter name for an entity property.
     *
     * @param mixed  $entity
     * @param string $property
     *
     * @return string
     *
     * @throws \LogicException if the entity has no getter for the requested property
     */
    private function getEntityGetter($entity, $property)
    {
        $getter = null;

        $prefixes = ['get', 'is', 'has', ''];

        foreach ($prefixes as $prefix) {
            $test = $prefix.ucfirst($property);
            if (method_exists($entity, $test)) {
                $getter = $test;
                break;
            }
        }

        if (null === $getter) {
            throw new \LogicException("Entity has no getter for property `{$property}`.");
        }

        return $getter;
    }

    /**
     * Gets the correct setter name for an entity property.
     *
     * @param mixed  $entity
     * @param string $property
     *
     * @return string
     *
     * @throws \LogicException if the entity has no setter for the requested property
     */
    private function getEntitySetter($entity, $property)
    {
        $setter = 'set'.ucfirst($property);
        if (!method_exists($entity, $setter)) {
            throw new \LogicException("Entity has no setter for property `{$property}`.");
        }

        return $setter;
    }

    private function toArray(\stdClass $data)
    {
        $asArray = [];

        foreach (array_keys(get_object_vars($data)) as $var) {
            $asArray[$var] = $data->{$var};
        }

        return $asArray;
    }
}
