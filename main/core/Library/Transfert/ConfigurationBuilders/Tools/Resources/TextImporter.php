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
use Claroline\CoreBundle\Library\Transfert\RichTextInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;

/**
 * @DI\Service("claroline.tool.resources.text_importer")
 * @DI\Tag("claroline.importer")
 */
class TextImporter extends Importer implements ConfigurationInterface, RichTextInterface
{
    private $container;
    private $om;

    /**
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container")
     * })
     */
    public function __construct($container)
    {
        $this->container = $container;
        $this->om = $this->container->get('claroline.persistence.object_manager');
    }

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('data');
        $this->addTextSection($rootNode);

        return $treeBuilder;
    }

    public function addTextSection($rootNode)
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

    public function import(array $array)
    {
        $ds = DIRECTORY_SEPARATOR;

        foreach ($array['data'] as $item) {
            $content = file_get_contents($this->getRootPath().$ds.$item['file']['path']);

            return $this->container->get('claroline.manager.text_manager')->create(
                $content,
                'title',
                $this->container->get('security.token_storage')->getToken()->getUser()
            );
        }
    }

    public static function fileNotExists($v, $rootpath)
    {
        $ds = DIRECTORY_SEPARATOR;

        return !file_exists($rootpath.$ds.$v);
    }

    public function export($workspace, array &$_files, $object)
    {
        $content = $this->om->getRepository('Claroline\CoreBundle\Entity\Resource\Revision')
            ->getLastRevision($object)->getContent();

        $uid = uniqid().'.txt';
        $tmpPath = $this->container->get('claroline.config.platform_config_handler')->getParameter('tmp_dir').DIRECTORY_SEPARATOR.$uid;
        file_put_contents($tmpPath, $content);
        $_files[$uid] = $tmpPath;
        $data = [['file' => [
            'path' => $uid,
        ]]];

        return $data;
    }

    public function getName()
    {
        return 'text';
    }

    public function format($data)
    {
        if (isset($data[0])) {
            if ($path = $data[0]['file']['path']) {
                $content = file_get_contents($this->getRootPath().DIRECTORY_SEPARATOR.$path);
                $entities = $this->om->getRepository('ClarolineCoreBundle:Resource\Revision')->findByContent($content);

                foreach ($entities as $entity) {
                    $text = $entity->getContent();
                    $text = $this->container->get('claroline.importer.rich_text_formatter')->format($text);
                    $entity->setContent($text);
                    $this->om->persist($entity);
                }
            }
        }
    }
}
