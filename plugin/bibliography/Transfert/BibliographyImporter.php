<?php

namespace Icap\BibliographyBundle\Transfert;

use Claroline\CoreBundle\Library\Transfert\Importer;
use Icap\BibliographyBundle\Manager\BookReferenceManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;

/**
 * @DI\Service("claroline.importer.icap_bibliography_importer")
 * @DI\Tag("claroline.importer")
 */
class BibliographyImporter extends Importer implements ConfigurationInterface
{
    /**
     * @var \Icap\BibliographyBundle\BookReferenceManager
     */
    private $bookReferenceManager;

    /**
     * @DI\InjectParams({
     *      "bookReferenceManager" = @DI\Inject("icap.bookreference.manager")
     * })
     */
    public function __construct(BookReferenceManager $bookReferenceManager)
    {
        $this->bookReferenceManager = $bookReferenceManager;
    }

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('data');
        $rootNode
            ->children()
                ->scalarNode('author')->isRequired()->end()
                ->scalarNode('description')->end()
                ->scalarNode('abstract')->end()
                ->scalarNode('isbn')->isRequired()->end()
                ->scalarNode('publisher')->end()
                ->scalarNode('printer')->end()
                ->integerNode('publicationYear')->end()
                ->scalarNode('language')->end()
                ->integerNode('pageCount')->end()
                ->scalarNode('url')->end()
                ->scalarNode('coverUrl')->end()
            ->end()
        ->end();

        return $treeBuilder;
    }

    public function getName()
    {
        return 'icap_bibliography';
    }

    public function validate(array $data)
    {
        $processor = new Processor();
        $processor->processConfiguration($this, $data);
    }

    public function import(array $data)
    {
        $rootPath = $this->getRootPath();
        $loggedUser = $this->getOwner();

        return $this->bookReferenceManager->import($data, $rootPath, $loggedUser);
    }

    public function export($workspace, array &$files, $object)
    {
        return $this->bookReferenceManager->export($workspace, $files, $object);
    }
}
