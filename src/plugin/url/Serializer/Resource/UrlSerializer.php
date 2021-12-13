<?php

namespace HeVinci\UrlBundle\Serializer\Resource;

use Claroline\AppBundle\API\Options;
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
            'id' => $url->getUuid(),
        ]);
    }

    /**
     * @param Url $url
     */
    public function deserialize($data, UrlInterface $url, array $options = []): UrlInterface
    {
        if (!in_array(Options::REFRESH_UUID, $options)) {
            $this->sipe('id', 'setUuid', $data, $url);
        } else {
            $url->refreshUuid();
        }

        return parent::deserialize($data, $url);
    }
}
