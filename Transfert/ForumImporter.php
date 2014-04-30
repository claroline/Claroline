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

/**
 * @DI\Service("claroline.importer.forum_importer")
 * @DI\Tag("claroline.importer")
 */
class ForumImporter extends Importer implements ConfigurationInterface
{
    /**
     * @DI\InjectParams({"om" = @DI\Inject("claroline.persistence.object_manager")})
     */
    public function __construct($om)
    {
        $this->om = $om;
    }

    public function  getConfigTreeBuilder()
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
            ->children()
                ->arrayNode('category')->isRequired()
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
                                                                ->scalarNode('content')->end()
                                                                ->scalarNode('creator')->end()
                                                                ->scalarNode('date_creation')->end()
                                                                ->scalarNode('date_modification')->end()
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

        foreach ($data['data'] as $category) {
            $entityCategory = new Category();
            $entityCategory->setForum($forum);
            $entityCategory->setName($category['category']['name']);
            $creator = $repo->findOneByUsername($category['category']);

            foreach ($category['category']['subjects'] as $subject) {
                $subjectEntity = new Subject();
                $subjectEntity->setTitle($subject['subject']['name']);
                $creator = $repo->findOneByUsername($subject['subject']['creator']);
                $subjectEntity->setCreator($creator);
                $subjectEntity->setCategory($entityCategory);

                foreach ($subject['subject']['messages'] as $message) {
                    $messageEntity = new Message();
                    $messageEntity->setContent($message['message']['content']);
                    $creator = $repo->findOneByUsername($message['message']['creator']);
                    $messageEntity->setCreator($creator);
                    $messageEntity->setSubject($subjectEntity);

                    $this->om->persist($messageEntity);
                }

                $this->om->persist($subjectEntity);
            }

            $this->om->persist($entityCategory);
        }

        return $forum;
    }
}