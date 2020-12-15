<?php

namespace HeVinci\UrlBundle\Serializer\Resource;

use HeVinci\UrlBundle\Entity\Url;
use HeVinci\UrlBundle\Model\UrlInterface;
use HeVinci\UrlBundle\Serializer\AbstractUrlSerializer;

class UrlSerializer extends AbstractUrlSerializer
{
    public function getName()
    {
        return 'url';
    }

    public function getClass()
    {
        return Url::class;
    }

    /**
     * @param Url $url
     */
    public function serialize(UrlInterface $url): array
    {
        return array_merge(parent::serialize($url), [
            'id' => $url->getId(),
        ]);
    }
}
