<?php

namespace Icap\LessonBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\File\PublicFileSerializer;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Icap\LessonBundle\Entity\Chapter;
use Icap\LessonBundle\Repository\ChapterRepository;

class ChapterSerializer
{
    use SerializerTrait;
    const INCLUDE_INTERNAL_NOTES = 'include_internal_notes';

    /** @var ObjectManager */
    private $om;

    /** @var PublicFileSerializer */
    private $fileSerializer;

    /** @var ChapterRepository */
    private $chapterRepository;

    public function __construct(ObjectManager $om, PublicFileSerializer $fileSerializer)
    {
        $this->om = $om;
        $this->fileSerializer = $fileSerializer;
        $this->chapterRepository = $om->getRepository(Chapter::class);
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return Chapter::class;
    }

    public function getName()
    {
        return 'lesson_chapter';
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
    public function serialize(Chapter $chapter, array $options = []): array
    {
        $previousChapter = $this->chapterRepository->getPreviousChapter($chapter);
        $nextChapter = $this->chapterRepository->getNextChapter($chapter);

        $serialized = [
            'id' => $chapter->getUuid(),
            'slug' => $chapter->getSlug(),
            'title' => $chapter->getTitle(),
            'poster' => $this->serializePoster($chapter),
            'text' => $chapter->getText(),
            'parentSlug' => $chapter->getParent() ? $chapter->getParent()->getSlug() : null,
            'previousSlug' => $previousChapter ? $previousChapter->getSlug() : null,
            'nextSlug' => $nextChapter ? $nextChapter->getSlug() : null,
        ];

        if (in_array(static::INCLUDE_INTERNAL_NOTES, $options)) {
            $serialized['internalNote'] = $chapter->getInternalNote();
        }

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

    public function deserialize(array $data, Chapter $chapter = null): Chapter
    {
        if (empty($chapter)) {
            $chapter = new Chapter();
        }

        $this->sipe('title', 'setTitle', $data, $chapter);
        $this->sipe('text', 'setText', $data, $chapter);
        $this->sipe('poster.url', 'setPoster', $data, $chapter);
        $this->sipe('internalNote', 'setInternalNote', $data, $chapter);

        return $chapter;
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
            'text' => $node['text'],
            'poster' => $node['poster'],
            'children' => $children,
        ];
    }

    private function serializePoster(Chapter $chapter)
    {
        $poster = null;
        if (!empty($chapter->getPoster())) {
            /** @var PublicFile $file */
            $file = $this->om
                ->getRepository('Claroline\CoreBundle\Entity\File\PublicFile')
                ->findOneBy(['url' => $chapter->getPoster()]);

            if ($file) {
                $poster = $this->fileSerializer->serialize($file);
            }
        }

        return $poster;
    }
}
