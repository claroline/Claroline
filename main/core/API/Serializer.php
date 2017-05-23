<?php

namespace Claroline\CoreBundle\API;

use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.API.serializer")
 */
class Serializer
{
    private $serializers;

    public function __construct()
    {
        $this->serializers = [];
    }

    public function addSerializer($serializer)
    {
        if (!method_exists($serializer, 'serialize')) {
            throw new \Exception('The serializer '.get_class($serializer).' must implement the method serialize');
        }

        $this->serializers[] = $serializer;
    }

    public function getSerializer($object)
    {
        foreach ($this->serializers as $serializer) {
            $p = new \ReflectionParameter([get_class($serializer), 'serialize'], 0);
            $className = $p->getClass()->getName();

            if ($object instanceof $className) {
                return $serializer;
            }
        }

        throw new \Exception('No serializer found for class '.get_class($object).'. Maybe you forgot to add the "claroline.serializer" tag to your serializer');
    }

    public function serialize($object)
    {
        return $this->getSerializer($object)->serialize($object);
    }
}
