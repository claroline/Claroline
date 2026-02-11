<?php

namespace Claroline\AppBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\Entity\AbstractUserView;
use Claroline\CommunityBundle\Serializer\UserSerializer;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;

class ViewerSerializer
{
    public function __construct(
        private readonly UserSerializer $userSerializer,
    ) {
    }

    public function serialize(AbstractUserView $viewer, ?array $options = []): array
    {
        return [
            'user' => $viewer->getUser() ? $this->userSerializer->serialize($viewer->getUser(), [SerializerInterface::SERIALIZE_MINIMAL]) : null,
            'seenAt' => DateNormalizer::normalize($viewer->getSeenAt()),
            'count' => $viewer->getCount(),
            'id' => $viewer->getId(),
        ];
    }
}
