<?php

namespace Icap\LessonBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Icap\LessonBundle\Entity\Lesson;

class LessonSerializer
{
    use SerializerTrait;

    public function getName(): string
    {
        return 'lesson';
    }

    public function getClass(): string
    {
        return Lesson::class;
    }

    public function getSchema(): string
    {
        return '#/plugin/lesson/lesson.json';
    }

    public function serialize(Lesson $lesson): array
    {
        return [
            'id' => $lesson->getUuid(),
            'display' => [
                'showOverview' => $lesson->getShowOverview(),
                'showMeta' => $lesson->getShowMeta(),
                'numbering' => $lesson->getNumbering(),
                'pagination' => $lesson->getPagination(),
                'navigation' => $lesson->hasNavigation(),
                'showSummary' => $lesson->getOpenSummary(),
            ],
        ];
    }

    public function deserialize(array $data, Lesson $lesson, ?array $options = []): Lesson
    {
        if (!in_array(SerializerInterface::REFRESH_UUID, $options)) {
            $this->sipe('id', 'setUuid', $data, $lesson);
        } else {
            $lesson->refreshUuid();
        }

        $this->sipe('display.showOverview', 'setShowOverview', $data, $lesson);
        $this->sipe('display.showMeta', 'setShowMeta', $data, $lesson);
        $this->sipe('display.numbering', 'setNumbering', $data, $lesson);
        $this->sipe('display.pagination', 'setPagination', $data, $lesson);
        $this->sipe('display.navigation', 'setNavigation', $data, $lesson);
        $this->sipe('display.showSummary', 'setOpenSummary', $data, $lesson);

        return $lesson;
    }
}
