<?php

namespace Claroline\CoreBundle\Library\Transfert\ConfigurationBuilders\Tools\Widgets;

use Claroline\CoreBundle\Library\Transfert\Importer;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.widget.text_importer")
 * @DI\Tag("claroline.importer")
 */
class TextImporter extends Importer implements ConfigurationInterface
{
    private $result;

    public function  getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('text');
        $this->addTextSection($rootNode);

        return $treeBuilder;
    }

    public function supports($type)
    {
        return $type == 'yml' ? true: false;
    }

    public function validate(array $data)
    {
        $processor = new Processor();
        $this->result = $processor->processConfiguration($this, $data);
    }

    public function import(array $array)
    {

    }

    public function getName()
    {
        return 'text';
    }

    public function addTextSection($rootNode)
    {
        $rootNode
            ->prototype('array')
                ->children()
                    ->scalarNode('locale')->isRequired()->end()
                    ->scalarNode('content')->isRequired()->end()
                ->end()
            ->end();
    }
} 