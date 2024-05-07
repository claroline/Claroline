<?php

namespace Icap\LessonBundle\Serializer;

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
                'description' => $lesson->getDescription(),
                'showOverview' => $lesson->getShowOverview(),
                'numbering' => $lesson->getNumbering(),
            ],
        ];
    }

    public function deserialize(array $data, Lesson $lesson = null): Lesson
    {
        if (empty($lesson)) {
            $lesson = new lesson();
        }

        $this->sipe('display.description', 'setDescription', $data, $lesson);
        $this->sipe('display.showOverview', 'setShowOverview', $data, $lesson);
        $this->sipe('display.numbering', 'setNumbering', $data, $lesson);

        return $lesson;
    }
}
