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

use Claroline\CoreBundle\API\SerializerProvider;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;

/**
 * @DI\Service
 * @DI\Tag("twig.extension")
 */
class SerializerExtension extends \Twig_Extension
{
    /** @var SerializerInterface */
    private $serializer;
    /** @var SerializerProvider */
    private $serializerProvider;

    /**
     * SerializerExtension constructor.
     *
     * @DI\InjectParams({
     *     "serializer"         = @DI\Inject("serializer"),
     *     "serializerProvider" = @DI\Inject("claroline.api.serializer")
     * })
     *
     * @param SerializerInterface $serializer
     * @param SerializerProvider  $serializerProvider
     */
    public function __construct(
        SerializerInterface $serializer,
        SerializerProvider $serializerProvider)
    {
        $this->serializer = $serializer;
        $this->serializerProvider = $serializerProvider;
    }

    public function getFilters()
    {
        return [
            'api_serialize' => new \Twig_Filter_Method($this, 'apiSerialize'),
            'json_serialize' => new \Twig_Filter_Method($this, 'serialize'),
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
        return $this->serializerProvider->serialize($object, $options);
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
        $context = new SerializationContext();

        if ($group) {
            $context->setGroups($group);
        }

        return $this->serializer->serialize($data, 'json', $context);
    }
}
