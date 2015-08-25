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
use Symfony\Component\HttpFoundation\File\File as SfFile;
use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Entity\Workspace\Workspace;

/**
 * @DI\Service("claroline.tool.resources.file_importer")
 * @DI\Tag("claroline.importer")
 */
class FileImporter extends Importer implements ConfigurationInterface
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
            ->prototype('array')
                ->children()
                    ->arrayNode('file')
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
                            ->scalarNode('mime_type')->end()
                        ->end()
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

    public function import(array $array, $name)
    {
        $ds = DIRECTORY_SEPARATOR;

        foreach ($array['data'] as $item) {
            $file = new File();
            $tmpFile = new SfFile($this->getRootPath() . $ds . $item['file']['path']);

            return $this->container->get('claroline.listener.file_listener')->createFile(
                $file, $tmpFile,  $name, $item['file']['mime_type']
            );
        }
    }

    public function export(Workspace $workspace, array &$_files, $object)
    {
        $hash = $object->getHashName();
        $uid = uniqid() . '.' . pathinfo($hash, PATHINFO_EXTENSION);
        $_files[$uid] = $this->container
            ->getParameter('claroline.param.files_directory') . DIRECTORY_SEPARATOR . $hash;
        $data = array();

        if (file_exists($_files[$uid])) {
            $data = array(array('file' => array(
                'path' => $uid,
                'mime_type' =>  $object->getResourceNode()->getMimeType()
            )));
        }

        return $data;
    }

    public function getName()
    {
        return 'file';
    }

    public static function fileNotExists($v, $rootpath)
    {
        $ds = DIRECTORY_SEPARATOR;

        return !file_exists($rootpath . $ds . $v);;
    }
}
