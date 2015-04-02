<?php

namespace Icap\BlogBundle\Transfer;

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Transfert\Importer;
use Icap\BlogBundle\Manager\BlogManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;

/**
 * @DI\Service("claroline.importer.icap_blog_importer")
 * @DI\Tag("claroline.importer")
 */
class BlogImporter extends Importer implements ConfigurationInterface
{
    /**
     * @var BlogManager
     */
    private $blogManager;

    /**
     * @DI\InjectParams({
     *      "blogManager" = @DI\Inject("icap_blog.manager.blog"),
     * })
     */
    public function __construct(BlogManager $blogManager)
    {
        $this->blogManager = $blogManager;
    }

    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('data');
        $rootNode
            ->children()
                ->arrayNode('options')
                    ->children()
                        ->booleanNode('authorize_comment')->defaultFalse()->end()
                        ->booleanNode('authorize_anonymous_comment')->defaultFalse()->end()
                        ->integerNode('post_per_page')->defaultValue(10)->end()
                        ->booleanNode('auto_publish_post')->defaultFalse()->end()
                        ->booleanNode('auto_publish_comment')->defaultFalse()->end()
                        ->booleanNode('display_title')->defaultTrue()->end()
                        ->booleanNode('banner_activate')->defaultTrue()->end()
                        ->booleanNode('display_post_view_counter')->defaultTrue()->end()
                        ->scalarNode('banner_background_color')->defaultValue('#FFFFFF')->end()
                        ->integerNode('banner_height')->defaultValue(100)->min(100)->end()
                        ->scalarNode('banner_background_image')->defaultNull()->end()
                        ->scalarNode('banner_background_image_position')->defaultValue('left top')->end()
                        ->scalarNode('banner_background_image_repeat')->defaultValue('no-repeat')->end()
                        ->integerNode('tag_cloud')->defaultValue(0)->min(0)->max(1)->end()
                    ->end()
                ->end()
                ->arrayNode('posts')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('title')->isRequired()->end()
                            ->scalarNode('content')->isRequired()->end()
                            ->scalarNode('author')->isRequired()->end()
                            ->integerNode('status')->defaultValue(0)->min(0)->max(1)->end()
                            ->scalarNode('creation_date')->isRequired()->end()
                            ->scalarNode('modification_date')->defaultNull()->end()
                            ->scalarNode('publication_date')->defaultNull()->end()
                            ->arrayNode('tags')
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('name')->isRequired()->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('comments')
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('message')->isRequired()->end()
                                        ->scalarNode('author')->isRequired()->end()
                                        ->scalarNode('creation_date')->isRequired()->end()
                                        ->scalarNode('update_date')->defaultNull()->end()
                                        ->scalarNode('publication_date')->defaultNull()->end()
                                        ->integerNode('status')->defaultValue(0)->min(0)->max(1)->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }

    function getName()
    {
        return 'icap_blog';
    }

    /**
     * @param array $data
     */
    function validate(array $data)
    {
        $processor = new Processor();
        $result = $processor->processConfiguration($this, $data);
    }

    /**
     * @param array  $data
     * @param string $name
     *
     * @return \Icap\BlogBundle\Entity\Blog
     */
    function import(array $data, $name)
    {
        return $this->blogManager->importBlog($data, $this->getRootPath(), $this->getOwner());
    }

    /**
     * @param Workspace $workspace
     * @param array     $files
     * @param mixed     $object
     *
     * @return array
     */
    function export(Workspace $workspace, array &$files, $object)
    {
        return $this->blogManager->exportBlog($workspace, $files, $object);
    }
}
