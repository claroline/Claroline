<?php
/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Transfert\ConfigurationBuilders\Tools\Resources;

use Claroline\CoreBundle\Library\Transfert\Importer;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Config\Definition\Processor;

/**
 * @DI\Service("claroline.tool.resources.file_importer")
 * @DI\Tag("claroline.importer")
 */
class FileImporter extends Importer implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('data');
        $this->addFileSection($rootNode);

        return $treeBuilder;
    }

    public function addFileSection($rootNode)
    {
        $rootPath = $this->getRootPath();

        $rootNode
            ->children()
                ->arrayNode('file')
                    ->children()
                        ->scalarNode('path')
                            ->validate()
                                ->ifTrue(
                                    function ($v) use ($rootPath) {
                                        return call_user_func_array(
                                            __CLASS__ . '::fileNotExists',
                                            array($v, $rootPath)
                                        );
                                    }
                                )
                                ->thenInvalid("The file %s doesn't exists")
                            ->end()
                        ->end()
                        ->scalarNode('mime_type')->end()
                    ->end()
                ->end()
            ->end()
        ->end();
    }

    public function supports($type)
    {
        return $type == 'yml' ? true: false;
    }

    public function validate(array $data)
    {
        $processor = new Processor();
        $processor->processConfiguration($this, $data);
    }

    public function import(array $array)
    {

    }

    public function getName()
    {
        return 'file_importer';
    }

    public static function fileNotExists($v, $rootpath)
    {
        $ds = DIRECTORY_SEPARATOR;

        return !file_exists($rootpath . $ds . $v);;
    }
} 