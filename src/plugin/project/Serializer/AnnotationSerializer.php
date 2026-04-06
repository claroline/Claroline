<?php

namespace Claroline\ProjectBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CommunityBundle\Serializer\UserSerializer;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\ProjectBundle\Entity\Annotation;

class AnnotationSerializer
{
    public function __construct(
        private readonly UserSerializer $userSerializer,
        private readonly ObjectManager $om
    ) {
    }

    public function getName(): string
    {
        return 'project_annotation';
    }

    public function getClass(): string
    {
        return Annotation::class;
    }

    public function serialize(Annotation $annotation): array
    {
        return [
            'id' => $annotation->getUuid(),
            'content' => $annotation->getContent(),
            'date' => DateNormalizer::normalize($annotation->getDate()),
            'milestone' => $annotation->getMilestone() ? $annotation->getMilestone()->getUuid() : null,
            'user' => $annotation->getUser() ?
                $this->userSerializer->serialize($annotation->getUser(), [SerializerInterface::SERIALIZE_MINIMAL]) : null,
        ];
    }
}
