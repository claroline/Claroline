<?php

namespace Icap\LessonBundle\Manager;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Icap\LessonBundle\Entity\Chapter;
use Icap\LessonBundle\Entity\Lesson;
use Icap\LessonBundle\Repository\ChapterRepository;

class ChapterManager
{
    private ChapterRepository $chapterRepository;

    public function __construct(
        private readonly ObjectManager $om,
        private readonly Crud $crud
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

        $this->copyChildren($rootOriginal, $rootCopy);
    }

    /**
     * @throws InvalidDataException
     */
    public function createChapter(Lesson $lesson, array $data = [], ?Chapter $parent = null, ?array $options = []): Chapter
    {
        $newChapter = new Chapter();
        $newChapter->setLesson($lesson);

        $this->crud->create($newChapter, $data, $options);

        $this->insertChapter($newChapter, $parent ?? $lesson->getRoot());

        return $newChapter;
    }

    public function moveChapter(Chapter $chapter, array $positionData): void
    {
        if ($positionData['parent']) {
            $parent = $this->chapterRepository->findOneBy(['slug' => $positionData['parent']]);
        } else {
            $parent = $chapter->getLesson()->getRoot();
        }

        $order = $positionData['order'];

        switch ($order) {
            case 'first':
                $this->chapterRepository->persistAsFirstChildOf($chapter, $parent);
                break;

            case 'before':
                $nextChapter = $this->chapterRepository->findOneBy(['slug' => $positionData['page']]);
                if ($nextChapter) {
                    $this->chapterRepository->persistAsPrevSiblingOf($chapter, $nextChapter);
                }
                break;

            case 'after':
                $previousChapter = $this->chapterRepository->findOneBy(['slug' => $positionData['page']]);
                if ($previousChapter) {
                    $this->chapterRepository->persistAsNextSiblingOf($chapter, $previousChapter);
                }
                break;

            case 'last':
                $this->chapterRepository->persistAsLastChildOf($chapter, $parent);
                break;
        }

        $this->om->persist($chapter);
        $this->om->flush();
    }

    /**
     * Copy chapter_org subchapters into provided chapter_copy.
     */
    private function copyChapter(Chapter $chapterOrg, Chapter $parent): void
    {
        $chapterCopy = new Chapter();
        $chapterCopy->setLesson($parent->getLesson());
        $chapterCopy->setTitle($chapterOrg->getTitle());
        $chapterCopy->setText($chapterOrg->getText());
        $chapterCopy->setInternalNote($chapterOrg->getInternalNote());

        $this->insertChapter($chapterCopy, $parent);

        $this->copyChildren($chapterOrg, $chapterCopy);
    }

    private function copyChildren(Chapter $chapterOrg, Chapter $chapterCopy): void
    {
        $chapters = $this->chapterRepository->children($chapterOrg, true);
        if (null !== $chapters && count($chapters) > 0) {
            foreach ($chapters as $child) {
                $this->copyChapter($child, $chapterCopy);
            }
        }
    }

    private function insertChapter(Chapter $chapter, Chapter $parent): void
    {
        $this->om->getRepository(Chapter::class)->persistAsLastChildOf($chapter, $parent);
        $this->om->flush();
    }
}
