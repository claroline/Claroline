<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 *
 * Date: 3/10/15
 */

namespace Icap\LessonBundle\Transfert;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Transfert\Importer;
use Claroline\CoreBundle\Library\Transfert\RichTextInterface;
use Icap\LessonBundle\Manager\LessonManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @DI\Service("claroline.importer.icap_lesson_importer")
 * @DI\Tag("claroline.importer")
 */
class LessonImporter extends Importer implements ConfigurationInterface, RichTextInterface
{
    /**
     * @var \Icap\LessonBundle\Manager\LessonManager
     */
    private $lessonManager;

    /**
     * @DI\InjectParams({
     *      "lessonManager" = @DI\Inject("icap.lesson.manager"),
     *      "om"            = @DI\Inject("claroline.persistence.object_manager"),
     *      "container"     = @DI\Inject("service_container")
     * })
     */
    public function __construct(
        LessonManager $lessonManager,
        ObjectManager $om,
        ContainerInterface $container
    ) {
        $this->lessonManager = $lessonManager;
        $this->om = $om;
        $this->container = $container;
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
        $rootNode
            ->children()
                ->arrayNode('chapters')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('id')->end()
                            ->scalarNode('parent_id')->end()
                            ->booleanNode('is_root')->defaultFalse()->end()
                            ->scalarNode('title')->end()
                            ->scalarNode('path')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();
    }

    public function validate(array $data)
    {
        $processor = new Processor();
        $processor->processConfiguration($this, $data);
    }

    public function import(array $data, $name)
    {
        return $this->lessonManager->importLesson($data, $this->getRootPath());
    }

    /**
     * @param Workspace $workspace
     * @param array     $files
     * @param mixed     $object
     *
     * @return array $data
     */
    public function export($workspace, array &$files, $object)
    {
        return $this->lessonManager->exportLesson($workspace, $files, $object);
    }

    public function format($data)
    {
        foreach ($data['chapters'] as $chapter) {
            //look for the text with the exact same content (it's really bad I know but at least it works
            $text = file_get_contents($this->getRootPath().DIRECTORY_SEPARATOR.$chapter['path']);
            $chapters = $this->om->getRepository('Icap\LessonBundle\Entity\Chapter')->findByText($text);

            foreach ($chapters as $entity) {
                //avoid circulary dependency
                $text = $this->container->get('claroline.importer.rich_text_formatter')->format($text);
                $entity->setText($text);
                $this->om->persist($entity);
            }
        }

        //this could be bad, but the corebundle can use a transaction and force flush itself anyway
        $this->om->flush();
    }
}
