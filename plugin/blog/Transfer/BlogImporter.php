<?php

namespace Icap\BlogBundle\Transfer;

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Transfert\Importer;
use Claroline\CoreBundle\Library\Transfert\RichTextInterface;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Icap\BlogBundle\Manager\BlogManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @DI\Service("claroline.importer.icap_blog_importer")
 * @DI\Tag("claroline.importer")
 */
class BlogImporter extends Importer implements ConfigurationInterface, RichTextInterface
{
    /**
     * @var BlogManager
     */
    private $blogManager;

    /**
     * @DI\InjectParams({
     *      "blogManager" = @DI\Inject("icap_blog.manager.blog"),
     *      "om"            = @DI\Inject("claroline.persistence.object_manager"),
     *      "container"     = @DI\Inject("service_container")
     * })
     */
    public function __construct(
        BlogManager $blogManager,
        ObjectManager $om,
        ContainerInterface $container
    ) {
        $this->blogManager = $blogManager;
        $this->om = $om;
        $this->container = $container;
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
                ->scalarNode('infos_path')
                    ->end()
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

    public function getName()
    {
        return 'icap_blog';
    }

    /**
     * @param array $data
     */
    public function validate(array $data)
    {
        $processor = new Processor();
        $processor->processConfiguration($this, $data);
    }

    /**
     * @param array  $data
     * @param string $name
     *
     * @return \Icap\BlogBundle\Entity\Blog
     */
    public function import(array $data, $name)
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
    public function export($workspace, array &$files, $object)
    {
        return $this->blogManager->exportBlog($workspace, $files, $object);
    }

    public function format($data)
    {
        foreach ($data['posts'] as $post) {
            //look for the text with the exact same content (it's really bad I know but at least it works
            $text = file_get_contents($this->getRootPath().DIRECTORY_SEPARATOR.$post['content']);
            $posts = $this->om->getRepository('Icap\BlogBundle\Entity\Post')->findByContent($text);

            foreach ($posts as $entity) {
                //avoid circulary dependency
                $text = $this->container->get('claroline.importer.rich_text_formatter')->format($text);
                $entity->setContent($text);
                $this->om->persist($entity);

                //format comments
                if (isset($post['comments']) && !empty($post['comments'])) {
                    foreach ($post['comments'] as $comment) {
                        $textCom = file_get_contents($this->getRootPath().DIRECTORY_SEPARATOR.$comment['message']);
                        if ($textCom !== '') {
                            $commentEntities = $this->om->getRepository('Icap\BlogBundle\Entity\Comment')->findByMessage($textCom);
                            foreach ($commentEntities as $commentEntity) {
                                $textCom = $this->container->get('claroline.importer.rich_text_formatter')->format($textCom);
                                $commentEntity->setMessage($textCom);
                                $this->om->persist($commentEntity);
                            }
                        }
                    }
                }
            }
        }

        //format infobar
        if (isset($data['infos_path'])) {
            $text = file_get_contents($this->getRootPath().DIRECTORY_SEPARATOR.$data['infos_path']);
            $infobars = $this->om->getRepository('Icap\BlogBundle\Entity\Blog')->findByInfos($text);
            foreach ($infobars as $entity) {
                //avoid circulary dependency
                $text = $this->container->get('claroline.importer.rich_text_formatter')->format($text);
                $entity->setInfos($text);
                $this->om->persist($entity);
            }
        }

        //this could be bad, but the corebundle can use a transaction and force flush itself anyway
        $this->om->flush();
    }
}
