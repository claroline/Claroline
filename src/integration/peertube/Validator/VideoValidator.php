<?php

namespace Claroline\PeerTubeBundle\Validator;

use Claroline\AppBundle\API\ValidatorInterface;
use Claroline\PeerTubeBundle\Entity\Video;
use Claroline\PeerTubeBundle\Manager\PeerTubeManager;

class VideoValidator implements ValidatorInterface
{
    public function __construct(
        private readonly PeerTubeManager $peerTubeManager
    ) {
    }

    public static function getClass(): string
    {
        return Video::class;
    }

    public function validate(array $data, string $mode, array $options = []): array
    {
        if (empty($data['url'])) {
            return [];
        }

        $error = $this->peerTubeManager->checkUrl($data['url']);
        if (!empty($error)) {
            return [
                [
                    'path' => 'url',
                    'message' => $error,
                ],
            ];
        }

        return [];
    }

    public function getUniqueFields(): array
    {
        return [
            'id' => 'uuid',
        ];
    }
}
