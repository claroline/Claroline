<?php

namespace Icap\LessonBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Icap\LessonBundle\Entity\Chapter;
use Icap\LessonBundle\Repository\ChapterRepository;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @DI\Service("icap.serializer.lesson.chapter")
 * @DI\Tag("claroline.serializer")
 */
class ChapterSerializer
{
    use SerializerTrait;

    /** @var ObjectManager */
    private $om;

    /** @var ChapterRepository */
    private $chapterRepository;

    /**
     * ChapterSerializer constructor.
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
        return 'Icap\LessonBundle\Entity\Chapter';
    }

    /**
     * @return string
     */
    public function getSchema()
    {
        return '#/plugin/lesson/chapter.json';
    }

    /**
     * Serializes a Chapter entity for the JSON api.
     *
     * @param Chapter $chapter - the Chapter resource to serialize
     * @param array   $options - a list of serialization options
     *
     * @return array - the serialized representation of the Chapter resource
     */
    public function serialize(Chapter $chapter, array $options = [])
    {
        $previousChapter = $this->chapterRepository->getPreviousChapter($chapter);
        $nextChapter = $this->chapterRepository->getNextChapter($chapter);

        $serialized = [
            'id' => $chapter->getUuid(),
            'slug' => $chapter->getSlug(),
            'title' => $chapter->getTitle(),
            'text' => $chapter->getText(),
            'parentSlug' => $chapter->getParent() ? $chapter->getParent()->getSlug() : null,
            'previousSlug' => $previousChapter ? $previousChapter->getSlug() : null,
            'nextSlug' => $nextChapter ? $nextChapter->getSlug() : null,
        ];

        return $serialized;
    }

    /**
     * Serializes a chapter tree, returned from Gedmo tree extension.
     *
     * @param $tree
     *
     * @return array
     */
    public function serializeChapterTree($tree)
    {
        return $this->serializeChapterTreeNode($tree);
    }

    private function serializeChapterTreeNode($node)
    {
        $children = [];
        if (!empty($node['__children'])) {
            foreach ($node['__children'] as $child) {
                $children[] = $this->serializeChapterTreeNode($child);
            }
        }

        return [
            'id' => $node['uuid'],
            'title' => $node['title'],
            'slug' => $node['slug'],
            'children' => $children,
        ];
    }

    /**
     * @param array          $data
     * @param Chapter | null $chapter
     *
     * @return Chapter - The deserialized chapter entity
     */
    public function deserialize($data, Chapter $chapter = null)
    {
        if (empty($chapter)) {
            $chapter = new Chapter();
            $chapter->refreshUuid();
        }
        $this->sipe('title', 'setTitle', $data, $chapter);
        $this->sipe('text', 'setText', $data, $chapter);

        if (empty($chapter->getTitle())) {
            throw new BadRequestHttpException('Title cannot be blank');
        }

        return $chapter;
    }
}
