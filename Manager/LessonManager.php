<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 * 
 * Date: 3/11/15
 */

namespace Icap\LessonBundle\Manager;

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use Icap\LessonBundle\Entity\Lesson;
use Icap\LessonBundle\Entity\Chapter;

/**
 * @DI\Service("icap.lesson.manager")
 */
class LessonManager
{
    /**
     * @var \Claroline\CoreBundle\Persistence\ObjectManager
     */
    private $om;

    private $chapterRepository;

    /**
     * @DI\InjectParams({
     *      "om"        = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
        $this->chapterRepository = $this->om->getRepository('IcapLessonBundle:Chapter');
    }

    /**
     * Imports lesson object from array
     * (see LessonImporter for structure and description).
     *
     * @param array $data
     * @param $rootPath
     *
     * @return Lesson
     */
    public function importLesson(array $data, $rootPath)
    {
        $lesson = new Lesson();
        if (isset($data['data'])) {
            $lessonData = $data['data'];

            $chaptersMap = array();
            foreach ($lessonData['chapters'] as $chapter) {
                $entityChapter = new Chapter();
                $entityChapter->setLesson($lesson);
                $entityChapter->setTitle($chapter['title']);
                $text = file_get_contents(
                    $rootPath.DIRECTORY_SEPARATOR.$chapter['path']
                );
                $entityChapter->setText($text);
                if ($chapter['is_root']) {
                    $lesson->setRoot($entityChapter);
                }
                $parentChapter = null;
                if ($chapter['parent_id'] !== null) {
                    $parentChapter = $chaptersMap[$chapter['parent_id']];
                    $entityChapter->setParent($parentChapter);
                    $this->chapterRepository->persistAsLastChildOf($entityChapter, $parentChapter);
                } else {
                    $this->chapterRepository->persistAsFirstChild($entityChapter);
                }
                $chaptersMap[$chapter['id']] = $entityChapter;
            }
        }

        return $lesson;
    }

    /**
     * Exports a Lesson resource
     * according to the description found in LessonImporter.
     *
     * @param Workspace $workspace
     * @param array     $files
     * @param Lesson    $object
     *
     * @return array
     */
    public function exportLesson(Workspace $workspace, array &$files, Lesson $object)
    {
        $data = array('chapters' => array());

        // Getting all sections and building array
        $rootChapter = $object->getRoot();
        $chapters = $this->chapterRepository->children($rootChapter);
        array_unshift($chapters, $rootChapter);
        foreach ($chapters as $chapter) {
            $uid = uniqid().'.txt';
            $tmpPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.$uid;
            file_put_contents($tmpPath, $chapter->getText());
            $files[$uid] = $tmpPath;

            $chapterArray = array(
                'id' => $chapter->getId(),
                'parent_id' => ($chapter->getParent() !== null) ? $chapter->getParent()->getId() : null,
                'is_root' => $chapter->getId() == $rootChapter->getId(),
                'title' => $chapter->getTitle(),
                'path' => $uid,
            );

            $data['chapters'][] = $chapterArray;
        }

        return $data;
    }
}
