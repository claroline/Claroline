<?php

namespace Innova\PathBundle\Transfer;

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Innova\PathBundle\Manager\PathManager;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Claroline\CoreBundle\Library\Transfert\Importer;

class PathImporter extends Importer implements ConfigurationInterface
{
    /**
     * Path manager
     * @var \Innova\PathBundle\Manager\PathManager
     */
    protected $pathManager;

    /**
     * Class constructor
     * @param \Innova\PathBundle\Manager\PathManager $pathManager
     */
    /*public function __construct(PathManager $pathManager)
    {
        $this->pathManager = $pathManager;
    }*/

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
                ->booleanNode('modified')->default(false)->end()

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

    public function import(array $data, $name)
    {
        return $this->pathManager->import($data);
    }

    public function export(Workspace $workspace, array &$files, $object)
    {
        return $this->pathManager->export($workspace, $files, $object);
    }
}