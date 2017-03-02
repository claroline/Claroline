<?php

namespace Innova\CollecticielBundle\Transfer;

use Claroline\CoreBundle\Library\Transfert\Importer;
use Claroline\CoreBundle\Library\Transfert\RichTextInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CollecticielImporter extends Importer implements ConfigurationInterface, RichTextInterface
{
    /**
     * We need to inject the whole service container
     * if we try to only inject PathManager, there is a crash because of a circular reference into services.
     *
     * @var ContainerInterface
     */
    protected $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'innova_collecticiel';
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
                ->scalarNode('instruction')->end()
                ->booleanNode('allow_workspace_resource')->end()
                ->booleanNode('allow_upload')->end()
                ->booleanNode('allow_url')->end()
                ->booleanNode('allow_rich_text')->end()
                ->booleanNode('manual_planning')->end()
                ->scalarNode('manual_state')->end()
                ->scalarNode('start_allow_drop')->end()
                ->scalarNode('end_allow_drop')->end()
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

    public function import(array $data, $name, $created)
    {
        return $this->container->get('innova.manager.collecticiel_manager')->import($data, $created, $this->getRootPath());
    }

    public function export($workspace, array &$files, $object)
    {
        return $this->container->get('innova.manager.collecticiel_manager')->export($workspace, $files, $object);
    }

    public function format($data)
    {
        if (isset($data)) {
            if ($path = $data['instruction']) {
                $content = file_get_contents($this->getRootPath().DIRECTORY_SEPARATOR.$path);
                $entities = $this->container->get('doctrine.orm.entity_manager')->getRepository('InnovaCollecticielBundle:Dropzone')->findByInstruction($content);

                foreach ($entities as $entity) {
                    $text = $entity->getInstruction();
                    $text = $this->container->get('claroline.importer.rich_text_formatter')->format($text);
                    $entity->setInstruction($text);
                    $this->container->get('doctrine.orm.entity_manager')->persist($entity);
                }
            }
        }
    }
}
