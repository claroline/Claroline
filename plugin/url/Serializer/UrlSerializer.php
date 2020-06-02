<?php

namespace HeVinci\UrlBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CoreBundle\Manager\Template\PlaceholderManager;
use HeVinci\UrlBundle\Entity\Url;

class UrlSerializer
{
    use SerializerTrait;

    /** @var PlaceholderManager */
    private $placeholderManager;

    /**
     * UrlSerializer constructor.
     *
     * @param PlaceholderManager $placeholderManager
     */
    public function __construct(PlaceholderManager $placeholderManager)
    {
        $this->placeholderManager = $placeholderManager;
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
            'url' => $this->placeholderManager->replacePlaceholders($url->getUrl() ?? ''),
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
