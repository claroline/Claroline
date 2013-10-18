<?php

namespace Icap\LessonBundle\Manager;

use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;
use Icap\LessonBundle\Entity\Lesson;
use Icap\LessonBundle\Entity\Chapter;

/**
 * @DI\Service("icap.lesson.manager.chapter")
 */
class ChapterManager
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "entityManager" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "translator" = @DI\Inject("translator")
     * })
     */
    public function __construct(EntityManager $entityManager, $translator)
    {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }

    /**
     * Copy full lesson chapters, from original root to copy root
     * @param Chapter $root_original
     * @param Chapter $root_copy
     * @param Lesson $newlesson
     */
    public function copyRoot(Chapter $root_original, Chapter $root_copy){
        $this->copyChildren($root_original, $root_copy, true);
    }

    /**
     * Copy chapter_org subchapters into provided chapter_copy
     * @param Chapter $root_original
     * @param Chapter $root_copy
     * @param Lesson $newlesson
     */
    public function copyChapter(Chapter $chapter_org, Chapter $parent, $copy_children, $renamefirst = false){
        $chapter_copy = new Chapter();
        if($renamefirst)
        {
            $chapter_copy->setTitle($this->translator->trans('copy_prefix', array(), 'icap_lesson').$chapter_org->getTitle());
        }else
        {
            $chapter_copy->setTitle($chapter_org->getTitle());
        }

        $chapter_copy->setText($chapter_org->getText());
        $chapter_copy->setLesson($parent->getLesson());
        $this->insertChapter($chapter_copy, $parent);
        if($copy_children){
            $this->copyChildren($chapter_org, $chapter_copy, $copy_children);
        }
        return $chapter_copy;
    }

    public function copyChildren(Chapter $chapter_org, Chapter $chapter_copy, $copy_children){
        $chapterRepository =  $this->entityManager->getRepository('IcapLessonBundle:Chapter');
        $chapters = $chapterRepository->children($chapter_org, true);
        if($chapters != null and count($chapters) > 0){
            foreach ($chapters as $child) {
                $this->copyChapter($child, $chapter_copy, $copy_children, false);
            }
        }
    }

    public function insertChapter(Chapter $chapter, Chapter $parent){
        $this->entityManager->getRepository('IcapLessonBundle:Chapter')->persistAsLastChildOf($chapter, $parent);
        $this->entityManager->flush();
    }

}