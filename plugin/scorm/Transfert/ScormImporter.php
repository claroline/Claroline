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
use Claroline\ScormBundle\Entity\Scorm12Resource;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ScormImporter extends Importer implements ConfigurationInterface
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
                    ->arrayNode('scorm')
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
                            ->scalarNode('version')->isRequired()
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

    public function export($workspace, array &$_files, $object)
    {
        $hash = $object->getHashName();
        $uid = uniqid().'.'.pathinfo($hash, PATHINFO_EXTENSION);
        $_files[$uid] = $this->container
            ->getParameter('claroline.param.files_directory').DIRECTORY_SEPARATOR.$hash;
        $data = [];
        $version = $object instanceof Scorm12Resource ? '1.2' : '2004';

        if (file_exists($_files[$uid])) {
            $data = [['scorm' => [
                'path' => $uid,
                'version' => $version,
            ]]];
        }

        return $data;
    }

    public function import(array &$array, $name)
    {
        $ds = DIRECTORY_SEPARATOR;

        foreach ($array['data'] as $item) {
            $tmpFile = new UploadedFile($this->getRootPath().$ds.$item['scorm']['path'], $name, null, null, null, true);

            return $this->container->get('claroline.manager.scorm_manager')->createScorm(
                $tmpFile,
                $name,
                $item['scorm']['version']
            );
        }
    }

    public function getName()
    {
        return 'claroline_scorm';
    }

    public static function fileNotExists($v, $rootpath)
    {
        $ds = DIRECTORY_SEPARATOR;

        return !file_exists($rootpath.$ds.$v);
    }
}
