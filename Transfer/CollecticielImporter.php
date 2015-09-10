<?php

namespace Innova\CollecticielBundle\Transfer;

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Transfert\RichTextInterface;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Claroline\CoreBundle\Library\Transfert\Importer;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CollecticielImporter extends Importer implements ConfigurationInterface
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
        return $this->container->get('innova.manager.collecticiel_manager')->import($data, $created);
    }

    public function export(Workspace $workspace, array &$files, $object)
    {
        return $this->container->get('innova.manager.collecticiel_manager')->export($workspace, $files, $object);
    }
}