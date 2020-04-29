<?php

namespace HeVinci\UrlBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use HeVinci\UrlBundle\Entity\Url;
use HeVinci\UrlBundle\Manager\UrlManager;

class UrlSerializer
{
    use SerializerTrait;

    /** @var UrlManager */
    private $urlManager;

    /**
     * UrlSerializer constructor.
     *
     * @param UrlManager $urlManager
     */
    public function __construct(UrlManager $urlManager)
    {
        $this->urlManager = $urlManager;
    }

    public function getName()
    {
        return 'url';
    }

    public function getClass()
    {
        return Url::class;
    }

    public function serialize(Url $url)
    {
        return [
            'id' => $url->getId(),
            'raw' => $url->getUrl(),
            'url' => $this->urlManager->replacePlaceholders($url->getUrl()),
            'mode' => $url->getMode() ?? Url::OPEN_REDIRECT,
            'ratio' => $url->getRatio() ?? 56.25,
        ];
    }

    public function deserialize($data, Url $url)
    {
        $this->sipe('raw', 'setUrl', $data, $url);
        $this->sipe('mode', 'setMode', $data, $url);
        $this->sipe('ratio', 'setRatio', $data, $url);

        return $url;
    }
}
