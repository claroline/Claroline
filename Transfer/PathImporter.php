<?php

namespace Innova\PathBundle\Transfer;

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Transfert\RichTextInterface;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Claroline\CoreBundle\Library\Transfert\Importer;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PathImporter extends Importer implements ConfigurationInterface, RichTextInterface
{
    /**
     * We need to inject the whole service container
     * if we try to only inject PathManager, there is a crash because of a circular reference into services
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
        return 'innova_path';
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
                ->scalarNode('description')->end()
                ->scalarNode('structure')->end()
                ->booleanNode('breadcrumbs')->end()
                ->booleanNode('modified')->defaultFalse()->end()

                // Steps
                ->arrayNode('steps')
                    ->children()

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

    public function format($data)
    {

    }

    public function import(array $data, $name)
    {
        return $this->container->get('innova_path.manager.path')->import($data);
    }

    public function export(Workspace $workspace, array &$files, $object)
    {
        return $this->container->get('innova_path.manager.path')->export($workspace, $files, $object);
    }
}