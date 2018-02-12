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

use Claroline\CoreBundle\Library\Transfert\Importer;
use Claroline\CoreBundle\Library\Transfert\RichTextInterface;
use Claroline\ForumBundle\Entity\Category;
use Claroline\ForumBundle\Entity\Forum;
use Claroline\ForumBundle\Entity\Message;
use Claroline\ForumBundle\Entity\Subject;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;

/**
 * @DI\Service("claroline.importer.forum_importer")
 * @DI\Tag("claroline.importer")
 */
class ForumImporter extends Importer implements ConfigurationInterface, RichTextInterface
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
                                                ->scalarNode('author')->end()
                                                ->scalarNode('creation_date')->end()
                                                ->booleanNode('sticked')->defaultFalse()->end()
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
        $processor->processConfiguration($this, $data);
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

                    if (isset($subject['subject']['sticked'])) {
                        $subjectEntity->setIsSticked($subject['subject']['sticked']);
                    }

                    if (isset($subject['subject']['creation_date'])) {
                        $subjectEntity->setCreationDate(new \DateTime($subject['subject']['creation_date']));
                    }

                    if ($creator === null) {
                        $creator = $this->container->get('security.context')->getToken()->getUser();
                    }

                    if (isset($subject['subject']['author'])) {
                        $subjectEntity->setAuthor($subject['subject']['author']);
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

                        if (isset($message['message']['creation_date'])) {
                            $messageEntity->setCreationDate(new \DateTime($message['message']['creation_date']));
                        }

                        if (isset($message['message']['modification_date'])) {
                            $messageEntity->setModificationDate(new \DateTime($message['message']['modification_date']));
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

        $this->om->persist($forum);

        return $forum;
    }

    public function export($workspace, array &$files, $object)
    {
        $categories = $object->getCategories();
        $data = [];

        foreach ($categories as $category) {
            $subjects = $category->getSubjects();
            $subjectsData = [];
            foreach ($subjects as $subject) {
                $subjectData['subject']['name'] = $subject->getTitle();
                $subjectData['subject']['author'] = $subject->getCreator()->getUsername();
                $subjectData['subject']['creator'] = $subject->getCreator()->getEmail();
                $subjectData['subject']['sticked'] = $subject->isSticked();
                $subjectData['subject']['creation_date'] = $subject->getCreationDate();

                $messages = $subject->getMessages();
                $messagesData = [];

                foreach ($messages as $message) {
                    $messageData['message']['author'] = $message->getCreator()->getUsername();
                    $messageData['message']['creator'] = $message->getCreator()->getEmail();
                    $messageData['message']['creation_date'] = $message->getCreationDate();
                    $messageData['message']['modification_date'] = $message->getModificationDate();

                    $hash = uniqid('msg').'.txt';
                    $tmpPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.$hash;
                    file_put_contents($tmpPath, $message->getContent());
                    $files[$hash] = $tmpPath;
                    $messageData['message']['path'] = $hash;

                    $messagesData[] = $messageData;
                }

                $subjectData['subject']['messages'] = $messagesData;

                $subjectsData[] = $subjectData;
            }

            $data[] = [
                'category' => [
                    'name' => $category->getName(),
                    'subjects' => $subjectsData,
                ],
            ];
        }

        return $data;
    }

    public function format($data)
    {
        if (isset($data)) {
            foreach ($data as $elem) {
                foreach ($elem['category']['subjects'] as $subjects) {
                    foreach ($subjects as $subject) {
                        foreach ($subject['messages'] as $message) {
                            //look for the text with the exact same content (it's really bad I know but at least it works
                            $text = file_get_contents($this->getRootPath().DIRECTORY_SEPARATOR.$message['message']['path']);
                            $messages = $this->om->getRepository('Claroline\ForumBundle\Entity\Message')->findByContent($text);

                            foreach ($messages as $entity) {
                                //avoid circulary dependency
                                $text = $this->container->get('claroline.importer.rich_text_formatter')->format($text);
                                $entity->setContent($text);
                                $this->om->persist($entity);
                            }
                        }
                    }
                }
            }
        }

        //this could be bad, but the corebundle can use a transaction and force flush itself anyway
        $this->om->flush();
    }
}
