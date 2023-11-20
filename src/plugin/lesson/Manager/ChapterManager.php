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
use Icap\LessonBundle\Repository\ChapterRepository;
use Icap\LessonBundle\Serializer\ChapterSerializer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ChapterManager
{
    private EntityManager $entityManager;
    private ChapterSerializer $chapterSerializer;
    private ObjectManager $om;
    private EventDispatcherInterface $eventDispatcher;
    private TranslatorInterface $translator;
    private ChapterRepository $chapterRepository;

    public function __construct(
        EntityManager $entityManager,
        ChapterSerializer $chapterSerializer,
        ObjectManager $om,
        EventDispatcherInterface $eventDispatcher,
        TranslatorInterface $translator
    ) {
        $this->entityManager = $entityManager;
        $this->chapterSerializer = $chapterSerializer;
        $this->om = $om;
        $this->eventDispatcher = $eventDispatcher;
        $this->translator = $translator;
        $this->chapterRepository = $this->om->getRepository(Chapter::class);
    }

    /**
     * Copy full lesson chapters, from original root to copy root.
     */
    public function copyRoot(Chapter $rootOriginal, Chapter $rootCopy)
    {
        $rootCopy->setTitle($rootOriginal->getTitle());
        $rootCopy->setText($rootOriginal->getText());
        $rootCopy->setInternalNote($rootOriginal->getInternalNote());
        $this->copyChildren($rootOriginal, $rootCopy, true);
    }

    /**
     * Copy chapterOrg subchapters into provided chapterCopy.
     *
     * @param bool   $copyChildren
     * @param Lesson $copyName
     *
     * @return Chapter $chapterCopy
     */
    public function copyChapter(Chapter $chapterOrg, Chapter $parent, $copyChildren, $copyName = null)
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

    public function copyChildren(Chapter $chapterOrg, Chapter $chapterCopy, $copyChildren)
    {
        $chapterRepository = $this->entityManager->getRepository(Chapter::class);
        $chapters = $chapterRepository->children($chapterOrg, true);
        if (null !== $chapters && count($chapters) > 0) {
            foreach ($chapters as $child) {
                $this->copyChapter($child, $chapterCopy, $copyChildren);
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

        if (null === $newChapter->getTitle()) {
            $order = $this->getChapterOrder($parent, $data);
            $newChapter->setTitle($this->translator->trans('chapter', ['%chapter%' => $order], 'lesson'));
        }

        $this->insertChapterInPlace($newChapter, $parent, $data);

        $this->dispatch(new LogChapterCreateEvent($lesson, $newChapter, []));

        return $newChapter;
    }

    public function updateChapter(Lesson $lesson, Chapter $chapter, $data)
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

    private function getChapterOrder($parent, $data): int
    {
        $position = $data['position'];
        $sibling = $data['order']['sibling'];
        $subchapter = $data['order']['subchapter'];

        switch ($position) {
            case 'subchapter':
                switch ($subchapter) {
                    case 'first':
                        $num = 1;
                        break;
                    case 'last':
                    default:
                        $chapters = $this->chapterRepository->getChapterChildren($parent);
                        $num = count($chapters) + 1;
                        break;
                }
                break;
            case 'sibling':
            default:
                $prevChapters = $this->chapterRepository->getPrevSiblings($parent);
                switch ($sibling) {
                    case 'before':
                        $num = count($prevChapters) + 1;
                        break;
                    case 'after':
                    default:
                        $num = count($prevChapters) + 2;
                        break;
                }
                break;
        }

        return $num;
    }
}
