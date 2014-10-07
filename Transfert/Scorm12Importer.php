<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ScormBundle\Transfert;

use Claroline\CoreBundle\Library\Transfert\Importer;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\HttpFoundation\File\File as SfFile;
use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Entity\Workspace\Workspace;

/**
 * @DI\Service("claroline.tool.resources.scorm12_importer")
 * @DI\Tag("claroline.importer")
 */
class Scorm12Importer extends Importer implements ConfigurationInterface
{
    private $container;

    /**
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container")
     * })
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

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
                ->arrayNode('scorm12')
                    ->children()
                        ->scalarNode('path')->isRequired()
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
                        ->scalarNode('version')->end()
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

    public function import(array &$array, $name)
    {
        $ds = DIRECTORY_SEPARATOR;

        foreach ($array['data'] as $item) {
            $tmpFile = new SfFile($this->getRootPath() . $ds . $item['scorm12']['path']);

            return $this->container->get('claroline.manager.scorm_manager')->createScorm12(
                $tmpFile,
                $tmpFile->getBasename()
            );
        }

    }

    public function export(Workspace $workspace, array &$files, $object)
    {
        return array();
    }

    public function getName()
    {
        return 'claroline_scorm_12';
    }

    public static function fileNotExists($v, $rootpath)
    {
        $ds = DIRECTORY_SEPARATOR;

        return !file_exists($rootpath . $ds . $v);;
    }
} 