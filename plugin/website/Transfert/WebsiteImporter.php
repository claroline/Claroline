<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 * 
 * Date: 3/12/15
 */

namespace Icap\WebsiteBundle\Transfert;

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Transfert\Importer;
use Icap\WebsiteBundle\Manager\WebsiteManager;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.importer.icap_website_importer")
 * @DI\Tag("claroline.importer")
 */
class WebsiteImporter extends Importer implements ConfigurationInterface
{
    /**
     * @var \Icap\WebsiteBundle\Manager\WebsiteManager
     */
    private $websiteManager;

    private $container;

    /**
     * @DI\InjectParams({
     *      "websiteManager"        = @DI\Inject("icap.website.manager"),
     *      "container"          = @DI\Inject("service_container")
     * })
     */
    public function __construct(WebsiteManager $websiteManager, $container)
    {
        $this->websiteManager = $websiteManager;
        $this->container = $container;
    }

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('data');
        $this->addWebsiteDescription($rootNode);

        return $treeBuilder;
    }

    public function getName()
    {
        return 'icap_website';
    }

    public function addWebsiteDescription($rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('options')
                    ->children()
                        ->booleanNode('copyright_enabled')->defaultFalse()->end()
                        ->scalarNode('copyright_text')->end()
                        ->scalarNode('analytics_provider')->end()
                        ->scalarNode('analytics_account_id')->end()
                        ->scalarNode('bg_color')->end()
                        ->scalarNode('bg_image')->end()
                        ->scalarNode('bg_repeat')->end()
                        ->scalarNode('bg_position')->end()
                        ->integerNode('total_width')->end()
                        ->scalarNode('banner_bg_color')->end()
                        ->scalarNode('banner_bg_image')->end()
                        ->scalarNode('banner_bg_repeat')->end()
                        ->scalarNode('banner_bg_position')->end()
                        ->integerNode('banner_height')->end()
                        ->booleanNode('banner_enabled')->end()
                        ->scalarNode('footer_bg_color')->end()
                        ->scalarNode('footer_bg_image')->end()
                        ->scalarNode('footer_bg_repeat')->end()
                        ->scalarNode('footer_bg_position')->end()
                        ->integerNode('footer_height')->end()
                        ->booleanNode('footer_enabled')->end()
                        ->scalarNode('menu_bg_color')->end()
                        ->scalarNode('section_bg_color')->end()
                        ->scalarNode('menu_border_color')->end()
                        ->scalarNode('menu_font_color')->end()
                        ->scalarNode('section_font_color')->end()
                        ->scalarNode('menu_hover_color')->end()
                        ->scalarNode('menu_font_family')->end()
                        ->scalarNode('menu_font_style')->end()
                        ->scalarNode('menu_font_size')->end()
                        ->scalarNode('menu_font_weight')->end()
                        ->integerNode('menu_width')->end()
                        ->scalarNode('menu_orientation')->end()
                        ->scalarNode('css_code_path')->end()
                        ->scalarNode('banner_text_path')->end()
                        ->scalarNode('footer_text_path')->end()
                    ->end()
                ->end()
                ->arrayNode('pages')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('id')->end()
                            ->scalarNode('parent_id')->end()
                            ->booleanNode('is_root')->defaultFalse()->end()
                            ->booleanNode('visible')->defaultTrue()->end()
                            ->booleanNode('is_homepage')->defaultFalse()->end()
                            ->scalarNode('creation_date')->end()
                            ->scalarNode('title')->end()
                            ->booleanNode('is_section')->defaultFalse()->end()
                            ->scalarNode('description')->end()
                            ->scalarNode('type')->end()
                            ->scalarNode('url')->end()
                            ->scalarNode('rich_text_path')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();
    }

    public function validate(array $data)
    {
        $processor = new Processor();
        $processor->processConfiguration($this, $data);
    }

    public function import(array $data)
    {
        $rootPath = $this->getRootPath();

        return $this->websiteManager->importWebsite($data, $rootPath);
    }

    /**
     * @param Workspace $workspace
     * @param array     $files
     * @param mixed     $object
     *
     * @return array $data
     */
    public function export(Workspace $workspace, array &$files, $object)
    {
        return $this->websiteManager->exportWebsite($workspace, $files, $object);
    }
}
