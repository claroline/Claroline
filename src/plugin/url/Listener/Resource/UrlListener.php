<?php

namespace HeVinci\UrlBundle\Listener\Resource;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Component\Resource\ResourceComponent;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use HeVinci\UrlBundle\Entity\Url;

class UrlListener extends ResourceComponent
{
    public function __construct(
        private readonly SerializerProvider $serializer
    ) {
    }

    public static function getName(): string
    {
        return 'hevinci_url';
    }

    /** @var Url $resource */
    public function open(AbstractResource $resource, bool $embedded = false): ?array
    {
        return [
            'url' => $this->serializer->serialize($resource),
        ];
    }
}
