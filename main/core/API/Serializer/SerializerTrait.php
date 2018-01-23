<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\API\Serializer;

use Claroline\CoreBundle\API\Utils\ArrayUtils;

trait SerializerTrait
{
    /** @var GenericSerializer */
    protected $genericSerializer;

    /** @var arrayUtils */
    private $arrayUtils;

    /**
     * Injects Serializer service.
     *
     * @DI\InjectParams({
     *      "serializer" = @DI\Inject("claroline.generic_serializer")
     * })
     *
     * @param GenericSerializer $serializer
     */
    public function setSerializer(GenericSerializer $serializer)
    {
        $this->genericSerializer = $serializer;
    }

    public function serialize($object, array $options = [])
    {
        return $this->genericSerializer->serialize($object, $options);
    }

    public function deserialize($data, $object, array $options = [])
    {
        return $this->genericSerializer->deserialize($data, $object, $options);
    }

    /**
     * @param $prop   - the property path
     * @param $setter - the setter to use
     * @param $data   - the data array
     * @param $object - the object to use the setter on
     */
    public function setIfPropertyExists($prop, $setter, $data, $object)
    {
        //date parsing just in case

        if (!$this->arrayUtils) {
            $this->arrayUtils = new ArrayUtils();
        }

        try {
            $value = $this->arrayUtils->get($data, $prop);

            $object->{$setter}($value);
        } catch (\Exception $e) {
        }
    }

    /**
     * Alias of setIfPropertyExists.
     *
     * @param $prop   - the property path
     * @param $setter - the setter to use
     * @param $data   - the data array
     * @param $object - the object to use the setter on
     */
    public function sipe($prop, $setter, $data, $object)
    {
        $this->setIfPropertyExists($prop, $setter, $data, $object);
    }
}
