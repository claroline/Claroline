<?php

namespace Icap\LessonBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Icap\LessonBundle\Entity\Chapter;
use Icap\LessonBundle\Entity\Lesson;
use Icap\LessonBundle\Repository\ChapterRepository;
use Icap\LessonBundle\Serializer\ChapterSerializer;

class ChapterManager
{
    private ChapterRepository $chapterRepository;

    public function __construct(
        private readonly ObjectManager $om,
        private readonly ChapterSerializer $chapterSerializer
    ) {
        $this->chapterRepository = $om->getRepository(Chapter::class);
    }

    /**
     * Copy full lesson chapters, from original root to copy root.
     */
    public function copyRoot(Chapter $rootOriginal, Chapter $rootCopy): void
    {
        $rootCopy->setTitle($rootOriginal->getTitle());
        $rootCopy->setText($rootOriginal->getText());
        $rootCopy->setInternalNote($rootOriginal->getInternalNote());
        $this->copyChildren($rootOriginal, $rootCopy, true);
    }

    /**
     * Copy chapter_org subchapters into provided chapter_copy.
     */
    public function copyChapter(Chapter $chapterOrg, Chapter $parent, $copyChildren, $copyName = null): Chapter
    {
        $chapterCopy = new Chapter();
        if (!$copyName) {
            $copyName = $chapterOrg->getTitle();
        }
        $chapterCopy->setTitle($copyName);
        $chapterCopy->setText($chapterOrg->getText());
        $chapterCopy->setInternalNote($chapterOrg->getInternalNote());
        $chapterCopy->setLesson($parent->getLesson());
        $this->insertChapter($chapterCopy, $parent);
        if ($copyChildren) {
            $this->copyChildren($chapterOrg, $chapterCopy, $copyChildren);
        }

        return $chapterCopy;
    }

    public function copyChildren(Chapter $chapterOrg, Chapter $chapterCopy, $copyChildren): void
    {
        $chapterRepository = $this->om->getRepository(Chapter::class);
        $chapters = $chapterRepository->children($chapterOrg, true);
        if (null !== $chapters && count($chapters) > 0) {
            foreach ($chapters as $child) {
                $this->copyChapter($child, $chapterCopy, $copyChildren);
            }
        }
    }

    public function insertChapter(Chapter $chapter, Chapter $parent): void
    {
        $this->om->getRepository(Chapter::class)->persistAsLastChildOf($chapter, $parent);
        $this->om->flush();
    }

    public function serializeChapterTree(Lesson $lesson): array
    {
        $tree = $this->om->getRepository(Chapter::class)->buildChapterTree($lesson->getRoot(), 'chapter.uuid, chapter.level, chapter.title, chapter.slug, chapter.text, chapter.poster');

        return $this->chapterSerializer->serializeChapterTree($tree[0]);
    }

    public function createChapter(Lesson $lesson, array $data = [], Chapter $parent = null): Chapter
    {
        $newChapter = $this->chapterSerializer->deserialize($data);
        $newChapter->setLesson($lesson);

        $this->insertChapterInPlace($newChapter, $parent, $data);

        return $newChapter;
    }

    public function updateChapter(Lesson $lesson, Chapter $chapter, $data): void
    {
        $newParent = $this->chapterRepository->findOneBySlug($data['parentSlug']);

        $this->chapterSerializer->deserialize($data, $chapter);

        // Should the chapter be moved ?
        if (isset($data['move'])) {
            $this->insertChapterInPlace($chapter, $newParent, $data);
        } else {
            $this->om->persist($chapter);
            $this->om->flush();
        }
    }

    public function deleteChapter(Lesson $lesson, Chapter $chapter, $withChildren = false): void
    {
        if ($withChildren) {
            $this->om->remove($chapter);
        } else {
            $this->chapterRepository->removeFromTree($chapter);
        }

        $this->om->flush();
    }

    private function insertChapterInPlace($chapter, $parent, $data): void
    {
        $position = $data['position'];
        $sibling = $data['order']['sibling'];
        $subchapter = $data['order']['subchapter'];

        switch ($position) {
            case 'subchapter':
                switch ($subchapter) {
                    case 'first':
                        $this->chapterRepository->persistAsFirstChildOf($chapter, $parent);
                        break;
                    case 'last':
                    default:
                        $this->chapterRepository->persistAsLastChildOf($chapter, $parent);
                        break;
                }
                break;
            case 'sibling':
            default:
                switch ($sibling) {
                    case 'before':
                        $previousChapter = $this->chapterRepository->getPreviousSibling($parent);
                        if ($previousChapter) {
                            $this->chapterRepository->persistAsNextSiblingOf($chapter, $previousChapter);
                        } else {
                            $this->chapterRepository->persistAsFirstChildOf($chapter, $parent->getParent());
                        }
                        break;
                    case 'after':
                    default:
                        $this->chapterRepository->persistAsNextSiblingOf($chapter, $parent);
                        break;
                }
                break;
        }

        $this->om->persist($chapter);
        $this->om->flush();
    }
}
