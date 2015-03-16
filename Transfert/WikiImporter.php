<?php
/**
 * This file is part of the Claroline Connect package
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 * 
 * Date: 3/9/15
 */

namespace Icap\WikiBundle\Transfert;


use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Transfert\Importer;
use Icap\WikiBundle\Manager\WikiManager;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.importer.icap_wiki_importer")
 * @DI\Tag("claroline.importer")
 */
class WikiImporter extends Importer implements ConfigurationInterface{

    /**
     * @var \Icap\WikiBundle\Manager\WikiManager
     */
    private $wikiManager;


    /**
     * @DI\InjectParams({
     *      "wikiManager"        = @DI\Inject("icap.wiki.manager"),
     * })
     */
    public function __construct(WikiManager $wikiManager)
    {
        $this->wikiManager = $wikiManager;
    }

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('data');
        $this->addWikiDescription($rootNode);

        return $treeBuilder;
    }

    public function getName()
    {
        return 'icap_wiki';
    }

    public function addWikiDescription($rootNode)
    {
        $rootPath = $this->getRootPath();
        $rootNode
            ->children()
                ->arrayNode('options')
                    ->children()
                        ->integerNode('mode')->defaultValue(0)->end()
                    ->end()
                ->end()
                ->arrayNode('sections')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('id')->end()
                            ->scalarNode('parent_id')->end()
                            ->booleanNode('is_root')->defaultFalse()->end()
                            ->booleanNode('visible')->defaultTrue()->end()
                            ->scalarNode('creation_date')->end()
                            ->scalarNode('author')->end()
                            ->booleanNode('deleted')->defaultFalse()->end()
                            ->scalarNode('deletion_date')->end()
                            ->arrayNode('contributions')
                                ->prototype('array')
                                    ->children()
                                        ->arrayNode('contribution')
                                            ->children()
                                                ->booleanNode('is_active')->defaultFalse()->end()
                                                ->scalarNode('title')->end()
                                                ->scalarNode('contributor')->end()
                                                ->scalarNode('creation_date')->end()
                                                ->scalarNode('path')->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();
    }

    public function validate(array $data)
    {
        $processor = new Processor();
        $result = $processor->processConfiguration($this, ['data' => $data]);
    }

    public function import(array $data)
    {
        $rootPath = $this->getRootPath();
        $loggedUser = $this->getOwner();

        return $this->wikiManager->importWiki($data, $rootPath, $loggedUser);
    }

    /**
     * @param Workspace $workspace
     * @param array $files
     * @param mixed $object
     *
     * @return array
     */
    public function export(Workspace $workspace, array &$files, $object)
    {
        return $this->wikiManager->exportWiki($workspace, $files, $object);
    }
}