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

use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Tag;
use JMS\Serializer\SerializationContext;

/**
 * @Service
 * @Tag("twig.extension")
 */
class SerializerExtension extends \Twig_Extension
{
    protected $container;

    /**
     * @InjectParams({
     *     "container" = @Inject("service_container")
     * })
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getFilters()
    {
        return array(
            'api_serialize' => new \Twig_Filter_Method($this, 'apiSerialize'),
            'json_serialize' => new \Twig_Filter_Method($this, 'serialize'),
        );
    }

    public function getName()
    {
        return 'serializer_extension';
    }

    /**
     * Serializes data to JSON using the "api" serialization group.
     *
     * @param mixed $data
     */
    public function apiSerialize($data)
    {
        return $this->serialize($data, 'api');
    }

    /**
     * Serializes data to JSON, optionally filtering on a serialization group.
     *
     * @param mixed  $data
     * @param string $group
     */
    public function serialize($data, $group = null)
    {
        $context = new SerializationContext();

        if ($group) {
            $context->setGroups($group);
        }

        return $this->container->get('serializer')->serialize($data, 'json', $context);
    }
}
