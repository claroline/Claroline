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

use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Claroline\CoreBundle\Library\Transfert\Importer;
use Claroline\CoreBundle\Library\Transfert\RichTextInterface;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Claroline\CoreBundle\Entity\Widget\SimpleTextConfig;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Persistence\ObjectManager;

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

    public function  getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('data');
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

    public function import(array $data, WidgetInstance $widgetInstance)
    {
        $widgetText = new SimpleTextConfig();
        $content = file_get_contents(
            $this->getRootPath() . DIRECTORY_SEPARATOR . $data[0]['content']
        );
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

    public function format($data)
    {

    }
} 