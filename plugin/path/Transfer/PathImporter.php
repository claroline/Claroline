<?php

namespace Innova\PathBundle\Transfer;

use Claroline\CoreBundle\Library\Transfert\Importer;
use Claroline\CoreBundle\Library\Transfert\RichTextInterface;
use Innova\PathBundle\Entity\Path\Path;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @DI\Service("innova_path.importer.path")
 * @DI\Tag("claroline.importer")
 */
class PathImporter extends Importer implements ConfigurationInterface, RichTextInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * PathImporter constructor.
     *
     * We need to inject the whole service container
     * if we try to only inject PathManager, there is a crash because of a circular reference into services.
     *
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container")
     * })
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'innova_path';
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority()
    {
        return 99;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('data');

        $rootNode
            ->children()
                // Path properties
                ->arrayNode('path')
                    ->children()
                        ->scalarNode('description')->end()
                        ->scalarNode('structure')->end()
                        ->booleanNode('breadcrumbs')->end()
                        ->booleanNode('summaryDisplayed')->end()
                        ->booleanNode('completeBlockingCondition')->end()
                        ->booleanNode('manualProgressionAllowed')->end()
                        ->booleanNode('modified')->end()
                        ->booleanNode('published')->end()
                    ->end()
                ->end()

                // Steps
                ->arrayNode('steps')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('uid')->end()
                            ->scalarNode('parent')->end()
                            ->scalarNode('activityId')->end()
                            ->scalarNode('activityNodeId')->end()
                            ->scalarNode('order')->end()
                            ->scalarNode('lvl')->end()

                            // Inherited resources
                            ->arrayNode('inheritedResources')
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('resource')->end()
                                        ->scalarNode('lvl')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function validate(array $data)
    {
        $processor = new Processor();
        $processor->processConfiguration($this, $data);
    }

    /**
     * Replace IDs with the new ones in texts and path structure.
     *
     * @param $data
     */
    public function format($data)
    {
        if (!empty($data['path']) && !empty($data['path']['structure'])) {
            if (!$data['path']['published'] || $data['path']['modified']) {
                // Only process Path structure if Path is not published or if has pending modification
                $structure = file_get_contents($this->getRootPath().DIRECTORY_SEPARATOR.$data['path']['structure']);
                $entities = $this->container->get('doctrine.orm.entity_manager')->getRepository('InnovaPathBundle:Path\Path')->findByStructure($structure);

                /** @var Path $entity */
                foreach ($entities as $entity) {
                    $text = $entity->getStructure();

                    // Format RichText
                    $text = $this->container->get('claroline.importer.rich_text_formatter')->format($text);

                    // Decode JSON to be able to replace IDs in structure

                    $entity->setStructure($text);
                    $this->container->get('doctrine.orm.entity_manager')->persist($entity);
                }
            } else {
                // else, structure can be recalculated from generated data
            }
        }
    }

    /**
     * Replace IDS with the new ones in step structure.
     *
     * @param \stdClass $step
     */
    protected function formatStep(\stdClass $step)
    {
        // Step ID

        // Replace Activity ID

        // Process children
        if (!empty($step->children)) {
            foreach ($step->children as $child) {
                $this->formatStep($child);
            }
        }
    }

    public function import(array $data, $name, $created)
    {
        // Retrieve the structure of the Path from file
        $structure = file_get_contents($this->getRootPath().DIRECTORY_SEPARATOR.$data['data']['path']['structure']);

        return $this->container->get('innova_path.manager.path')->import($structure, $data, $created);
    }

    public function export($workspace, array &$files, $object)
    {
        return $this->container->get('innova_path.manager.path')->export($object, $files);
    }
}
