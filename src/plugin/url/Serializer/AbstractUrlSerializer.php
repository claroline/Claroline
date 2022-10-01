<?php

namespace HeVinci\UrlBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CoreBundle\Manager\Template\PlaceholderManager;
use HeVinci\UrlBundle\Model\UrlInterface;

abstract class AbstractUrlSerializer
{
    use SerializerTrait;

    /** @var PlaceholderManager */
    private $placeholderManager;

    public function __construct(PlaceholderManager $placeholderManager)
    {
        $this->placeholderManager = $placeholderManager;
    }

    public function serialize(UrlInterface $url): array
    {
        return [
            'raw' => $url->getUrl(),
            'url' => $this->placeholderManager->replacePlaceholders($url->getUrl() ?? ''),
            'mode' => $url->getMode(),
            'ratio' => $url->getRatio(),
        ];
    }

    public function deserialize($data, UrlInterface $url): UrlInterface
    {
        $this->sipe('raw', 'setUrl', $data, $url);
        $this->sipe('mode', 'setMode', $data, $url);
        $this->sipe('ratio', 'setRatio', $data, $url);

        return $url;
    }
}
