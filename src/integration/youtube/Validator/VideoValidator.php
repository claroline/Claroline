<?php

namespace Claroline\YouTubeBundle\Validator;

use Claroline\AppBundle\API\ValidatorInterface;
use Claroline\YouTubeBundle\Entity\Video;
use Claroline\YouTubeBundle\Manager\YouTubeManager;

class VideoValidator implements ValidatorInterface
{
    private YouTubeManager $youTubeManager;

    public function __construct(YouTubeManager $youTubeManager)
    {
        $this->youTubeManager = $youTubeManager;
    }

    public static function getClass(): string
    {
        return Video::class;
    }

    public function validate($data, $mode, array $options = []): array
    {
        if (empty($data['url'])) {
            return [];
        }

        $error = $this->youTubeManager->checkUrl($data['url']);
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
