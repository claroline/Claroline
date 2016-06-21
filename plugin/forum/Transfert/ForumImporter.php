<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ForumBundle\Transfert;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Claroline\CoreBundle\Library\Transfert\Importer;
use Symfony\Component\Config\Definition\Processor;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\ForumBundle\Entity\Forum;
use Claroline\ForumBundle\Entity\Category;
use Claroline\ForumBundle\Entity\Subject;
use Claroline\ForumBundle\Entity\Message;
use Claroline\CoreBundle\Entity\Workspace\Workspace;

/**
 * @DI\Service("claroline.importer.forum_importer")
 * @DI\Tag("claroline.importer")
 */
class ForumImporter extends Importer implements ConfigurationInterface
{
    private $container;
    private $om;

    /**
     * @DI\InjectParams({
     *      "om"        = @DI\Inject("claroline.persistence.object_manager"),
     *      "container" = @DI\Inject("service_container")
     * })
     */
    public function __construct($om, $container)
    {
        $this->container = $container;
        $this->om = $om;
    }

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('data');
        $this->addForumDescription($rootNode);

        return $treeBuilder;
    }

    public function getName()
    {
        return 'claroline_forum';
    }

    public function addForumDescription($rootNode)
    {
        $rootNode
            ->prototype('array')
                ->children()
                    ->arrayNode('category')
                        ->children()
                            ->scalarNode('name')->isRequired()->end()
                            ->arrayNode('subjects')
                                ->prototype('array')
                                    ->children()
                                        ->arrayNode('subject')
                                            ->children()
                                                ->scalarNode('name')->end()
                                                ->scalarNode('creator')->end()
                                                ->arrayNode('messages')
                                                    ->prototype('array')
                                                        ->children()
                                                            ->arrayNode('message')
                                                                ->children()
                                                                    ->scalarNode('path')->end()
                                                                    ->scalarNode('creator')->end()
                                                                    ->scalarNode('author')->end()
                                                                    ->scalarNode('creation_date')->end()
                                                                    ->scalarNode('modification_date')->end()
                                                                ->end()
                                                            ->end()
                                                        ->end()
                                                    ->end()
                                                ->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();
    }

    public function validate(array $data)
    {
        $processor = new Processor();
        $result = $processor->processConfiguration($this, $data);
    }

    public function import(array $data)
    {
        $forum = new Forum();
        $repo = $this->om->getRepository('ClarolineCoreBundle:User');

        if (isset($data['data'])) {
            foreach ($data['data'] as $category) {
                $entityCategory = new Category();
                $entityCategory->setForum($forum);
                $entityCategory->setName($category['category']['name']);

                foreach ($category['category']['subjects'] as $subject) {
                    $subjectEntity = new Subject();
                    $subjectEntity->setTitle($subject['subject']['name']);

                    $creator = null;

                    if ($subject['subject']['creator'] !== null) {
                        $creator = $repo->findOneByUsername($subject['subject']['creator']);
                    }

                    if ($creator === null) {
                        $creator = $this->container->get('security.context')->getToken()->getUser();
                    }

                    $subjectEntity->setCreator($creator);
                    $subjectEntity->setCategory($entityCategory);

                    foreach ($subject['subject']['messages'] as $message) {
                        $messageEntity = new Message();
                        $content = file_get_contents(
                            $this->getRootPath().DIRECTORY_SEPARATOR.$message['message']['path']
                        );

                        $messageEntity->setContent($content);

                        $creator = null;

                        if ($message['message']['creator'] !== null) {
                            $creator = $repo->findOneByUsername($message['message']['creator']);
                        }

                        if ($creator === null) {
                            $creator = $this->container->get('security.context')->getToken()->getUser();
                        }

                        $messageEntity->setCreator($creator);
                        $messageEntity->setSubject($subjectEntity);
                        $messageEntity->setAuthor($message['message']['author']);

                        $this->om->persist($messageEntity);
                    }

                    $this->om->persist($subjectEntity);
                }

                $this->om->persist($entityCategory);
            }
        }

        return $forum;
    }

    public function export(Workspace $workspace, array &$files, $object)
    {
        $categories = $object->getCategories();
        $data = [];

        foreach ($categories as $category) {
            $data[] = array(
                'category' => array(
                    'name' => $category->getName(),
                    'subjects' => array(),
                ),
            );
        }

        return $data;
    }
}
