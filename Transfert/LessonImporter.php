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
use Icap\LessonBundle\Manager\LessonManager;
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

    /**
     * @var \Icap\LessonBundle\Manager\LessonManager
     */
    private $lessonManager;

    /**
     * @DI\InjectParams({
     *      "lessonManager"        = @DI\Inject("icap.lesson.manager")
     * })
     */
    public function __construct(LessonManager $lessonManager)
    {
        $this->lessonManager = $lessonManager;
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
        return $this->lessonManager->importLesson($data, $this->getRootPath());
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
        return $this->lessonManager->exportLesson($workspace, $files, $object);
    }
}