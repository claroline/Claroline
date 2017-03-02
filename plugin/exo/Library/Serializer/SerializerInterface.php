<?php

namespace UJM\ExoBundle\Library\Serializer;

interface SerializerInterface
{
    /**
     * Converts entity into a JSON-encodable structure.
     *
     * @param mixed $entity
     * @param array $options
     *
     * @return mixed
     */
    public function serialize($entity, array $options = []);

    /**
     * Converts raw data into entities.
     *
     * @param \stdClass $data    - the data to deserialize
     * @param mixed     $entity  - the entity to populate (if null, a new one is created)
     * @param array     $options - the deserialization options
     *
     * @return mixed
     */
    public function deserialize($data, $entity = null, array $options = []);
}
