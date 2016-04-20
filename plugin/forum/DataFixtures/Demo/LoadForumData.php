<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ForumBundle\DataFixtures\Demo;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Fixtures\LoggableFixture;
use Claroline\ForumBundle\Entity\Forum;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\ForumBundle\Entity\Message;
use Claroline\ForumBundle\Entity\Subject;

class LoadForumData extends LoggableFixture implements ContainerAwareInterface
{
    /** @var ContainerInterface $container */
    private $container;

    private $username;
    private $forumName;
    private $nbMessages;
    private $nbSubjects;
    private $parent;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function __construct($forumName, $username, $nbMessages, $nbSubjects, ResourceNode $parent = null)
    {
        $this->forumName = $forumName;
        $this->username = $username;
        $this->nbMessages = $nbMessages;
        $this->nbSubjects = $nbSubjects;
        $this->parent = $parent;
    }

    public function load(ObjectManager $manager)
    {
        $creator = $this->getContainer()->get('claroline.manager.resource_manager');
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $user = $em->getRepository('ClarolineCoreBundle:User')->findOneBy(array('username' => $this->username));
        if ($this->parent == null) {
            $root = $em->getRepository('ClarolineCoreBundle:Resource\ResourceNode')
                ->findOneBy(array('parent' => null, 'workspace' => $user->getPersonalWorkspace()->getId()));
        } else {
            $root = $this->parent;
        }

        $collaborators = $em->getRepository('ClarolineCoreBundle:User')->findByWorkspace($root->getWorkspace());
        $maxOffset = count($collaborators);
        $this->log('collaborators found: '.count($collaborators));
        --$maxOffset;
        $forum = new Forum();
        $forum->setName($this->forumName);
        $forum = $creator->create(
            $forum,
            $manager->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneByName('claroline_forum'),
            $user,
            $root->getWorkspace(),
            $root
        );

        $forumManager = $this->getContainer()->get('claroline.manager.forum_manager');
        $category = $forumManager->createCategory($forum, $this->forumName);
        $this->log("forum {$forum->getName()} created");

        for ($i = 0; $i < $this->nbSubjects; ++$i) {
            $title = $this->container->get('claroline.utilities.lipsum_generator')->generateLipsum(5, false, 255);
            $user = $collaborators[rand(0, $maxOffset)];
            $subject = new Subject();
            $subject->setTitle($title);
            $subject->setCreator($user);
            $this->log("subject $title created");
            $subject->setCategory($category);
            $manager->persist($subject);

            for ($j = 0; $j < $this->nbMessages; ++$j) {
                $sender = $collaborators[rand(0, $maxOffset)];
                $message = new Message();
                $message->setSubject($subject);
                $message->setCreator($sender);
                $lipsum = $this->container->get('claroline.utilities.lipsum_generator')->generateLipsum(150, true, 1023);
                $message->setContent($lipsum);
                $manager->persist($message);
            }
            $manager->flush();
        }

        $manager->flush();

        $this->addReference("forum/{$this->forumName}", $forum);
    }
}
