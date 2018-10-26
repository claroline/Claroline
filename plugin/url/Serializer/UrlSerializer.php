<?php

namespace HeVinci\UrlBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use HeVinci\UrlBundle\Entity\Url;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.url")
 * @DI\Tag("claroline.serializer")
 */
class UrlSerializer
{
    use SerializerTrait;

    public function serialize(Url $url)
    {
        return [
            'id' => $url->getId(),
            'url' => $url->getUrl(),
            'mode' => null !== $url->getMode() && '' !== $url->getMode() ? $url->getMode() : 'redirect',
            'ratio' => $url->getRatio() ? $url->getRatio() : 56.25,
        ];
    }

    public function getClass()
    {
        return Url::class;
    }

    public function deserialize($data, Url $url)
    {
        $this->sipe('url', 'setUrl', $data, $url);
        $this->sipe('mode', 'setMode', $data, $url);
        $this->sipe('ratio', 'setRatio', $data, $url);

        return $url;
    }
}
