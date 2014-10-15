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
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Entity\Workspace\Workspace;

/**
 * @DI\Service("claroline.tool.resources.text_importer")
 * @DI\Tag("claroline.importer")
 */
class TextImporter extends Importer implements ConfigurationInterface
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
        $om = $this->container->get('claroline.persistence.object_manager');
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
        $ds = DIRECTORY_SEPARATOR;

        foreach ($array['data'] as $item) {
            $content = file_get_contents($this->getRootPath() . $ds . $item['file']['path']);
            return $this->container->get('claroline.manager.text_manager')->create(
                $content,
                'title',
                $this->container->get('security.context')->getToken()->getUser()
            );
        }
    }

    public static function fileNotExists($v, $rootpath)
    {
        $ds = DIRECTORY_SEPARATOR;

        return !file_exists($rootpath . $ds . $v);
    }

    public function export(Workspace $workspace, array &$files, $object)
    {
        $content = $this->om->getRepository('Claroline\CoreBundle\Entity\Revision')
            ->getLastRevision($object)->getContent();

        return array();
    }

    public function getName()
    {
        return 'text';
    }
} 