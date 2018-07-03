<?php

namespace Icap\LessonBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Icap\LessonBundle\Entity\Lesson;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @DI\Service("icap.serializer.lesson")
 * @DI\Tag("claroline.serializer")
 */
class LessonSerializer
{
    use SerializerTrait;

    /** @var ObjectManager */
    private $om;

    /** @var ChapterRepository */
    private $chapterRepository;

    /**
     * LessonSerializer constructor.
     *
     * @DI\InjectParams({
     *     "om"        = @DI\Inject("claroline.persistence.object_manager"),
     *     "container" = @DI\Inject("service_container")
     * })
     *
     * @param ObjectManager      $om
     * @param ContainerInterface $container
     */
    public function __construct(ObjectManager $om, ContainerInterface $container)
    {
        $this->om = $om;
        $this->chapterRepository = $container->get('doctrine.orm.entity_manager')->getRepository('IcapLessonBundle:Chapter');
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return 'Icap\LessonBundle\Entity\Lesson';
    }

    /**
     * @return string
     */
    public function getSchema()
    {
        return '#/plugin/lesson/lesson.json';
    }

    /**
     * Serializes a Lesson entity for the JSON api.
     *
     * @param Lesson $lesson  - the Lesson resource to serialize
     * @param array  $options - a list of serialization options
     *
     * @return array - the serialized representation of the Lesson resource
     */
    public function serialize(Lesson $lesson, array $options = [])
    {
        $firstChapter = $this->chapterRepository->getFirstChapter($lesson);

        $serialized = [
            'id' => $lesson->getUuid(),
            'title' => $lesson->getResourceNode()->getName(),
            'firstChapterId' => $firstChapter ? $firstChapter->getUuid() : null,
            'firstChapterSlug' => $firstChapter ? $firstChapter->getSlug() : null,
        ];

        return $serialized;
    }

    /**
     * @param array         $data
     * @param Lesson | null $lesson
     *
     * @return Lesson - The deserialized lesson entity
     */
    public function deserialize($data, Lesson $lesson = null)
    {
        if (empty($lesson)) {
            $lesson = new lesson();
            $lesson->refreshUuid();
        }
        $this->sipe('title', 'setTitle', $data, $lesson);

        if (empty($lesson->getTitle())) {
            throw new BadRequestHttpException('Title cannot be blank');
        }

        return $lesson;
    }
}
