<?php

namespace Claroline\ProjectBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\CommunityBundle\Serializer\UserSerializer;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\ProjectBundle\Entity\Annotation;
use Claroline\ProjectBundle\Entity\Submission;

class SubmissionSerializer
{
    public function __construct(
        private readonly UserSerializer $userSerializer,
        private readonly AnnotationSerializer $annotationSerializer
    ) {
    }

    public function getName(): string
    {
        return 'project_submission';
    }

    public function getClass(): string
    {
        return Submission::class;
    }

    public function serialize(Submission $submission): array
    {
        return [
            'id' => $submission->getUuid(),
            'contentType' => $submission->getContentType(),
            'textContent' => $submission->getTextContent(),
            'fileData' => $submission->getFileData(),
            'urlContent' => $submission->getUrlContent(),
            'submittedDate' => DateNormalizer::normalize($submission->getSubmittedDate()),
            'firstAccessDate' => DateNormalizer::normalize($submission->getFirstAccessDate()),
            'milestone' => $submission->getMilestone() ? $submission->getMilestone()->getUuid() : null,
            'user' => $submission->getUser() ?
                $this->userSerializer->serialize($submission->getUser(), [SerializerInterface::SERIALIZE_MINIMAL]) : null,
            'annotations' => array_map(function (Annotation $annotation) {
                return $this->annotationSerializer->serialize($annotation);
            }, $submission->getAnnotations()),
        ];
    }
}
