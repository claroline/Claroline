<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nicolas
 * Date: 18/10/13
 * Time: 09:36
 * To change this template use File | Settings | File Templates.
 */

namespace Icap\LessonBundle\Repository;

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Icap\LessonBundle\Entity\Chapter;
use Icap\LessonBundle\Entity\Lesson;

class ChapterRepository extends NestedTreeRepository{

    function getFirstChapter(Lesson $lesson){
        return $this->findOneBy(array('lesson' => $lesson, 'root' => $lesson->getRoot()->getId(), 'left' => 2));
    }

    function getChapterTree(Chapter $chapter){
        return $this->childrenHierarchy($chapter, false, array(), true);
    }
    function getChapterAndChapterChildren(Chapter $chapter){
        return $this->children($chapter, false, null, 'ASC', true);
    }

    function getChapterChildren(Chapter $chapter){
        return $this->children($chapter, false, null, 'ASC', false);
    }

    function getChapterAndDirectChapterChildren(Chapter $chapter){
        return $this->children($chapter, true, null, 'ASC', true);
    }

    function getDirectChapterChildren(Chapter $chapter){
        return $this->children($chapter, true, null, 'ASC', false);
    }


}