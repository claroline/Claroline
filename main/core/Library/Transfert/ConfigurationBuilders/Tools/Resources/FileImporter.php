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

use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Library\Transfert\Importer;
use Claroline\CoreBundle\Library\Transfert\RichTextInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\HttpFoundation\File\File as SfFile;

/**
 * @DI\Service("claroline.tool.resources.file_importer")
 * @DI\Tag("claroline.importer")
 */
class FileImporter extends Importer implements ConfigurationInterface, RichTextInterface
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
                            ->scalarNode('mime_type')->end()
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
        foreach ($array['data'] as $item) {
            $file = new File();
            $tmpFile = new SfFile($this->getRootPath().DIRECTORY_SEPARATOR.$item['file']['path']);

            $file = $this->container->get('claroline.listener.file_listener')->createFile(
                $file, $tmpFile,  $name, $item['file']['mime_type'], $workspace
            );

            return $file;
        }

        return $this->container->get('claroline.listener.file_listener')->createFile(
            new File(), new SfFile(tempnam('/tmp', 'claroimport')),  $name, 'none', $workspace
        );
    }

    public function export($workspace, array &$_files, $object)
    {
        $hash = $object->getHashName();
        $uid = uniqid().'.'.pathinfo($hash, PATHINFO_EXTENSION);
        $hash = $this->container->getParameter('claroline.param.files_directory').DIRECTORY_SEPARATOR.$hash;

        $_files[$uid] = $hash;
        $data = [];

        if (file_exists($_files[$uid])) {
            $data = [['file' => [
              'path' => $uid,
              'mime_type' => $object->getResourceNode()->getMimeType(),
          ]]];
        }

        return $data;
    }

    // data will usually look like this:
    //  item:
    //    name: Test_image.html
    //    creator: null
    //    parent: 0
    //    type: file
    //    roles:
    //        -
    //             role: { name: ROLE_WS_COLLABORATOR, rights: { open: true, export: true } }
    //    uid: 4
    //    data:
    //        -
    //             file: { path: 57a1a8a17766f.html, mime_type: text/html }
    /**
     * @param array $data The file informations from the yml file,
     */
    public function format($data)
    {
        if ($this->container->get('claroline.config.platform_config_handler')->getParameter('enable_rich_text_file_import')) {
            $em = $this->container->get('doctrine.orm.entity_manager');

            if (isset($data[0])) {
                if (strpos('_'.$data[0]['file']['mime_type'], 'text') > 0) {
                    $foundEntity = null;
                    $filePath = null;
                    $path = $data[0]['file']['path'];
                    //very dirty check. Waiting uid for a better one.
                    $content = file_get_contents(realpath($this->getRootPath()).DIRECTORY_SEPARATOR.$path);
                    $entities = $em->getRepository('ClarolineCoreBundle:Resource\ResourceNode')
                        ->findByWorkspaceAndMimeType($this->getWorkspace(), 'text');

                    //search the entity...
                    foreach ($entities as $entity) {
                        $hashName = $this->container->get('claroline.manager.resource_manager')->getResourceFromNode($entity)
                            ->getHashName();
                        $path = $this->container->getParameter('claroline.param.files_directory').DIRECTORY_SEPARATOR.$hashName;

                        if (file_get_contents($path) === $content) {
                            $foundEntity = $entity;
                            $filePath = $path;
                        }
                    }

                    //did we really find something ?
                    if ($foundEntity && $filePath && file_get_contents($filePath)) {
                        $text = $this->container->get('claroline.importer.rich_text_formatter')->format(file_get_contents($filePath));
                        file_put_contents($filePath, $text);
                    }
                }
            }
        }
    }

    public function getName()
    {
        return 'file';
    }

    public static function fileNotExists($v, $rootpath)
    {
        $ds = DIRECTORY_SEPARATOR;

        return !file_exists($rootpath.$ds.$v);
    }
}
