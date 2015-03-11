<?php
/**
 * This file is part of the Claroline Connect package
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 * 
 * Date: 3/10/15
 */

namespace Icap\LessonBundle\Transfert;


use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Transfert\Importer;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Icap\LessonBundle\Entity\Chapter;
use Icap\LessonBundle\Entity\Lesson;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.importer.icap_lesson_importer")
 * @DI\Tag("claroline.importer")
 */
class LessonImporter extends Importer implements ConfigurationInterface{

    private $container;
    private $om;

    /**
     * @DI\InjectParams({
     *      "om"        = @DI\Inject("claroline.persistence.object_manager"),
     *      "container" = @DI\Inject("service_container")
     * })
     */
    public function __construct($om, $container)
    {
        $this->container = $container;
        $this->om = $om;
    }

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('data');
        $this->addLessonDescription($rootNode);

        return $treeBuilder;
    }

    public function getName()
    {
        return 'icap_lesson';
    }

    public function addLessonDescription($rootNode)
    {
        $rootPath = $this->getRootPath();
        $rootNode
            ->children()
                ->arrayNode('chapter')
                    ->children()
                        ->scalarNode('id')->end()
                        ->scalarNode('parent_id')->end()
                        ->booleanNode('is_root')->defaultFalse()->end()
                        ->scalarNode('title')->end()
                        ->scalarNode('path')->end()
                    ->end()
                ->end()
            ->end()
        ->end();
    }

    public function validate(array $data)
    {
        $processor = new Processor();
        $result = $processor->processConfiguration($this, $data);
    }

    public function import(array $data)
    {
        $lesson = new Lesson();
        if (isset($data['data'])) {
            $lessonData = $data['data'];
            $chapterRepository = $this->om->getRepository("IcapLessonBundle:Chapter");

            $chaptersMap = array();
            foreach ($lessonData as $chapter) {
                $chapterData = $chapter['chapter'];
                $entityChapter = new Chapter();
                $entityChapter->setLesson($lesson);
                $entityChapter->setTitle($chapterData['title']);
                $text = file_get_contents(
                    $this->getRootPath() . DIRECTORY_SEPARATOR . $chapterData['path']
                );
                $entityChapter->setText($text);
                if ($chapterData['is_root']) {
                    $lesson->setRoot($entityChapter);
                }
                $parentChapter = null;
                if ($chapterData['parent_id'] !== null) {
                    $parentChapter = $chaptersMap[$chapterData['parent_id']];
                    $entityChapter->setParent($parentChapter);
                    $chapterRepository->persistAsLastChildOf($entityChapter, $parentChapter);
                } else {
                    $chapterRepository->persistAsFirstChild($entityChapter);
                }
                $chaptersMap[$chapterData['id']] = $entityChapter;
            }
        }

        return $lesson;
    }

    /**
     * @param Workspace $workspace
     * @param array $files
     * @param mixed $object
     *
     * @return array $data
     */
    public function export(Workspace $workspace, array &$files, $object)
    {
        $chapterRepository = $this->om->getRepository('IcapLessonBundle:Chapter');
        $data = array();

        // Getting all sections and building array
        $rootChapter = $object->getRoot();
        $chapters = $chapterRepository->children($rootChapter);
        array_unshift($chapters, $rootChapter);
        foreach ($chapters as $chapter) {
            $uid = uniqid() . '.txt';
            $tmpPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $uid;
            file_put_contents($tmpPath, $chapter->getText());
            $files[$uid] = $tmpPath;

            $chapterArray = array(
                'id'                => $chapter->getId(),
                'parent_id'         => ($chapter->getParent() !== null)?$chapter->getParent()->getId():null,
                'is_root'           => $chapter->getId() == $rootChapter->getId(),
                'title'             => $chapter->getTitle(),
                'path'              => $uid
            );

            $data[] = array(
                'chapter' => $chapterArray
            );
        }

        return $data;
    }
}