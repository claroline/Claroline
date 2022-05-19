<?php

namespace Icap\LessonBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Icap\LessonBundle\Entity\Chapter;
use Icap\LessonBundle\Entity\Lesson;
use Icap\LessonBundle\Event\Log\LogChapterCreateEvent;
use Icap\LessonBundle\Event\Log\LogChapterDeleteEvent;
use Icap\LessonBundle\Event\Log\LogChapterMoveEvent;
use Icap\LessonBundle\Event\Log\LogChapterUpdateEvent;
use Icap\LessonBundle\Serializer\ChapterSerializer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ChapterManager
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /** @var ChapterSerializer */
    protected $chapterSerializer;

    /** @var ObjectManager */
    protected $om;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var ChapterRepository */
    protected $chapterRepository;

    /**
     * Constructor.
     *
     * @param $eventDispatcher  $eventDispatcher
     */
    public function __construct(
        EntityManager $entityManager,
        ChapterSerializer $chapterSerializer,
        ObjectManager $om,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->entityManager = $entityManager;
        $this->chapterSerializer = $chapterSerializer;
        $this->om = $om;
        $this->eventDispatcher = $eventDispatcher;
        $this->chapterRepository = $entityManager->getRepository(Chapter::class);
    }

    /**
     * Copy full lesson chapters, from original root to copy root.
     */
    public function copyRoot(Chapter $root_original, Chapter $root_copy)
    {
        $root_copy->setTitle($root_original->getTitle());
        $root_copy->setText($root_original->getText());
        $root_copy->setInternalNote($root_original->getInternalNote());
        $this->copyChildren($root_original, $root_copy, true);
    }

    /**
     * Copy chapter_org subchapters into provided chapter_copy.
     *
     * @param bool   $copy_children
     * @param Lesson $copyName
     *
     * @return Chapter $chapter_copy
     */
    public function copyChapter(Chapter $chapter_org, Chapter $parent, $copy_children, $copyName = null)
    {
        $chapter_copy = new Chapter();
        if (!$copyName) {
            $copyName = $chapter_org->getTitle();
        }
        $chapter_copy->setTitle($copyName);
        $chapter_copy->setText($chapter_org->getText());
        $chapter_copy->setInternalNote($chapter_org->getInternalNote());
        $chapter_copy->setLesson($parent->getLesson());
        $this->insertChapter($chapter_copy, $parent);
        if ($copy_children) {
            $this->copyChildren($chapter_org, $chapter_copy, $copy_children);
        }

        return $chapter_copy;
    }

    public function copyChildren(Chapter $chapter_org, Chapter $chapter_copy, $copy_children)
    {
        $chapterRepository = $this->entityManager->getRepository(Chapter::class);
        $chapters = $chapterRepository->children($chapter_org, true);
        if (null !== $chapters && count($chapters) > 0) {
            foreach ($chapters as $child) {
                $this->copyChapter($child, $chapter_copy, $copy_children);
            }
        }
    }

    public function insertChapter(Chapter $chapter, Chapter $parent)
    {
        $this->entityManager->getRepository(Chapter::class)->persistAsLastChildOf($chapter, $parent);
        $this->entityManager->flush();
    }

    public function serializeChapterTree(Lesson $lesson)
    {
        $tree = $this->entityManager->getRepository(Chapter::class)->buildChapterTree($lesson->getRoot(), 'chapter.uuid, chapter.level, chapter.title, chapter.slug, chapter.text, chapter.poster');

        return $this->chapterSerializer->serializeChapterTree($tree[0]);
    }

    public function createChapter(Lesson $lesson, $data, $parent)
    {
        $newChapter = $this->chapterSerializer->deserialize($data);
        $newChapter->setLesson($lesson);

        $this->insertChapterInPlace($newChapter, $parent, $data);

        $this->dispatch(new LogChapterCreateEvent($lesson, $newChapter, []));

        return $newChapter;
    }

    public function updateChapter(lesson $lesson, Chapter $chapter, $data)
    {
        $oldParent = $chapter->getParent();
        $newParent = $this->chapterRepository->findOneBySlug($data['parentSlug']);

        $this->chapterSerializer->deserialize($data, $chapter);

        // Should the chapter be moved ?
        if (isset($data['move'])) {
            $this->insertChapterInPlace($chapter, $newParent, $data);
            $this->dispatch(new LogChapterMoveEvent($chapter->getLesson(), $chapter, $oldParent, $chapter->getParent()));
        } else {
            $this->om->persist($chapter);
            $this->om->flush();
        }

        $this->dispatch(new LogChapterUpdateEvent($lesson, $chapter, []));
    }

    public function deleteChapter(Lesson $lesson, Chapter $chapter, $withChildren = false)
    {
        if ($withChildren) {
            $this->om->remove($chapter);
        } else {
            $this->chapterRepository->removeFromTree($chapter);
        }

        $this->om->flush();

        $this->dispatch(new LogChapterDeleteEvent($lesson, $chapter, []));
    }

    private function insertChapterInPlace($chapter, $parent, $data)
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

    private function dispatch($event)
    {
        $this->eventDispatcher->dispatch($event, 'log');
    }
}
