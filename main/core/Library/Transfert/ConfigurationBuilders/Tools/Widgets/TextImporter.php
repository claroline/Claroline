<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Transfert\ConfigurationBuilders\Tools\Widgets;

use Claroline\CoreBundle\Entity\Widget\SimpleTextConfig;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Claroline\CoreBundle\Library\Transfert\Importer;
use Claroline\CoreBundle\Library\Transfert\RichTextInterface;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;

/**
 * @DI\Service("claroline.widget.text_importer")
 * @DI\Tag("claroline.importer")
 */
class TextImporter extends Importer implements ConfigurationInterface, RichTextInterface
{
    private $om;
    private $container;
    /**
     * @DI\InjectParams({
     *      "om"        = @DI\Inject("claroline.persistence.object_manager"),
     *      "container" = @DI\Inject("service_container")
     * })
     */
    public function __construct(ObjectManager $om, $container)
    {
        $this->om = $om;
        $this->container = $container;
    }

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('data');
        $this->addTextSection($rootNode);

        return $treeBuilder;
    }

    public function supports($type)
    {
        return $type === 'yml' ? true : false;
    }

    public function validate(array $data)
    {
        $processor = new Processor();
        $this->result = $processor->processConfiguration($this, $data);
    }

    public function import(array $data, WidgetInstance $widgetInstance)
    {
        $widgetText = new SimpleTextConfig();
        $content = '';

        if ($data[0]['content']) {
            $content = file_get_contents($this->getRootPath().DIRECTORY_SEPARATOR.$data[0]['content']);
        }

        $widgetText->setContent($content);
        $widgetText->setWidgetInstance($widgetInstance);
        $this->om->persist($widgetText);
        $this->om->flush();
    }

    public function getName()
    {
        return 'simple_text';
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

    public function export($workspace, array &$files, $object)
    {
        $txtConfig = $this->container->get('claroline.manager.simple_text_manager')->getTextConfig($object);
        $tmpPath = $this->container->get('claroline.config.platform_config_handler')->getParameter('tmp_dir').DIRECTORY_SEPARATOR.uniqid().'.txt';
        $content = $txtConfig ? $txtConfig->getContent() : '';
        file_put_contents($tmpPath, $content);
        $archPath = 'widgets/text/'.uniqid().'.txt';
        //create file
        $data = [['locale' => 'fr', 'content' => $archPath]];
        $files[$archPath] = $tmpPath;

        return $data;
    }

    public function format($data)
    {
        if ($data[0]['content']) {
            $content = file_get_contents($this->getRootPath().DIRECTORY_SEPARATOR.$data[0]['content']);
            $entities = $this->om->getRepository('ClarolineCoreBundle:Widget\SimpleTextConfig')->findByContent($content);

            foreach ($entities as $entity) {
                $text = $entity->getContent();
                $text = $this->container->get('claroline.importer.rich_text_formatter')->format($text);
                $entity->setContent($text);
                $this->om->persist($entity);
            }
        }
    }
}
