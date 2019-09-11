<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AppBundle\API\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Utils\ArrayUtils;
use Claroline\AppBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;

trait SerializerTrait
{
    /**
     * SetIfPropertyExists.
     * Sets an entity prop from an array data source.
     *
     * @param $prop   - the property path
     * @param $setter - the setter to use
     * @param $data   - the data array
     * @param $object - the object to use the setter on
     */
    public function sipe($prop, $setter, $data = [], $object, $trim = true)
    {
        if ($data && is_array($data)) {
            try {
                $value = ArrayUtils::get($data, $prop);

                if (is_string($value) && $trim) {
                    $value = trim($value);
                }

                $object->{$setter}($value);
            } catch (\Exception $e) {
            }
        }
    }

    public function getUuid($object, array $options): string
    {
        return in_array(Options::REFRESH_UUID, $options) ?
            $object->generateUuid() :
            $object->getUuid();
    }

    /**
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
     * alias method.
     */
    public function findInCollection($object, $method, $id, $class = null)
    {
        foreach ($object->$method() as $el) {
            if ($el->getId() === $id || $el->getUuid() === $id) {
                return $el;
            }
        }

        if ($class) {
            return $this->om->getObject(['id' => $id], $class);
        }
    }
}
