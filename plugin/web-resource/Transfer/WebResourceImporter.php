<?php
/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\WebResourceBundle\Transfer;

use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Library\Transfert\Importer;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\HttpFoundation\File\File as SfFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @DI\Service("claroline.tool.resources.web_resource")
 * @DI\Tag("claroline.importer")
 */
class WebResourceImporter extends Importer implements ConfigurationInterface
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
                                                    __CLASS__.'::fileNotExists',
                                                    [$v, $rootPath]
                                                );
                                            }
                                        )
                                        ->thenInvalid("The file %s doesn't exists")
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    public function supports($type)
    {
        return $type === 'yml' ? true : false;
    }

    public function validate(array $data)
    {
        $processor = new Processor();
        $processor->processConfiguration($this, $data);
    }

    public function import(array $array, $name, $created, $workspace)
    {
        $ds = DIRECTORY_SEPARATOR;

        foreach ($array['data'] as $item) {
            $tmpFile = new UploadedFile($this->getRootPath().$ds.$item['file']['path'], $name, null, null, null, true);

            return $this->container->get('claroline.listener.web_resource_listener')->create($tmpFile, $workspace);
        }

        return $this->container->get('claroline.listener.file_listener')->createFile(
            new File(), new SfFile(tempnam('/tmp', 'claroimport')),  $name, 'none', $workspace
        );
    }

    public function export($workspace, array &$_files, $object)
    {
        $hash = $object->getHashName();
        $uid = uniqid().'.'.pathinfo($hash, PATHINFO_EXTENSION);
        $_files[$uid] = $this->container
            ->getParameter('claroline.param.files_directory').DIRECTORY_SEPARATOR.$hash;
        $data = [];

        if (file_exists($_files[$uid])) {
            $data = [['file' => [
                'path' => $uid,
            ]]];
        }

        return $data;
    }

    public function getName()
    {
        return 'claroline_web_resource';
    }

    public static function fileNotExists($v, $rootpath)
    {
        $ds = DIRECTORY_SEPARATOR;

        return !file_exists($rootpath.$ds.$v);
    }
}
