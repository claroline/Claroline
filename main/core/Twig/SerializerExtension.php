<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Twig;

use Claroline\AppBundle\API\SerializerProvider;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service
 * @DI\Tag("twig.extension")
 */
class SerializerExtension extends \Twig_Extension
{
    /** @var SerializerProvider */
    private $serializerProvider;

    /**
     * SerializerExtension constructor.
     *
     * @DI\InjectParams({
     *     "serializerProvider" = @DI\Inject("claroline.api.serializer")
     * })
     *
     * @param SerializerProvider $serializerProvider
     */
    public function __construct(
        SerializerProvider $serializerProvider)
    {
        $this->serializerProvider = $serializerProvider;
    }

    /* JMS\Serializer */
    public function getFilters()
    {
        return [
            'api_serialize' => new \Twig_SimpleFilter('api_serialize', [$this, 'apiSerialize']),
            'json_serialize' => new \Twig_SimpleFilter('json_serialize', [$this, 'serialize']),
        ];
    }

    public function getName()
    {
        return 'serializer_extension';
    }

    /**
     * Serializes data to JSON using the SerializerProvider.
     *
     * @param mixed $object
     * @param array $options
     *
     * @return mixed
     */
    public function apiSerialize($object, $options = [])
    {
        if (!empty($object)) {
            return $this->serializerProvider->serialize($object, $options);
        }

        return $object;
    }

    /**
     * Serializes data to JSON, optionally filtering on a serialization group.
     *
     * @param mixed  $data
     * @param string $group
     *
     * @deprecated serialization should be handled by SerializerProvider
     */
    public function serialize($data, $group = null)
    {
        throw new \Exception('Use api_serialize instead');
    }
}
