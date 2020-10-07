<?php

namespace Icap\LessonBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Icap\LessonBundle\Entity\Lesson;

class LessonSerializer
{
    use SerializerTrait;

    public function getName()
    {
        return 'lesson';
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return Lesson::class;
    }

    /**
     * @return string
     */
    public function getSchema()
    {
        return '#/plugin/lesson/lesson.json';
    }

    public function serialize(Lesson $lesson, array $options = []): array
    {
        return [
            'id' => $lesson->getUuid(),
            'display' => [
                'description' => $lesson->getDescription(),
                'showOverview' => $lesson->getShowOverview(),
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

        return $lesson;
    }
}
