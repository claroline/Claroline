<?php

namespace Claroline\EvaluationBundle\Serializer\Certificate;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\CommunityBundle\Serializer\UserSerializer;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\EvaluationBundle\Entity\Certificate\WorkspaceCertificate;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class WorkspaceCertificateSerializer
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly UserSerializer $userSerializer
    ) {
    }

    public function getName(): string
    {
        return 'workspace_certificate';
    }

    public function getClass(): string
    {
        return WorkspaceCertificate::class;
    }

    public function serialize(WorkspaceCertificate $certificate, ?array $options = []): array
    {
        $serialized = [
            'id' => $certificate->getUuid(),
            'obtentionDate' => DateNormalizer::normalize($certificate->getObtentionDate()),
            'issueDate' => DateNormalizer::normalize($certificate->getIssueDate()),
            'status' => $certificate->getStatus(),
            'score' => $certificate->getScore(),
            'language' => $certificate->getLanguage(),
        ];

        if (!in_array(SerializerInterface::SERIALIZE_MINIMAL, $options)) {
            $admin = $this->authorization->isGranted('ADMINISTRATE', $certificate);
            $serialized['permissions'] = [
                'open' => $admin || $this->authorization->isGranted('OPEN', $certificate),
                'administrate' => $admin,
            ];

            $serialized['content'] = $certificate->getContent();
            $serialized['user'] = $this->userSerializer->serialize($certificate->getUser(), [SerializerInterface::SERIALIZE_MINIMAL]);
        }

        return $serialized;
    }
}
