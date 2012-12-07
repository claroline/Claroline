<?php

namespace Claroline\ForumBundle\Tests\DataFixtures;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Fixtures\LoggableFixture;
use Claroline\ForumBundle\Entity\Forum;
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

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function __construct($forumName, $username, $nbMessages, $nbSubjects)
    {
        $this->forumName = $forumName;
        $this->username = $username;
        $this->nbMessages = $nbMessages;
        $this->nbSubjects = $nbSubjects;
    }

    public function load(ObjectManager $manager)
    {
        $creator = $this->getContainer()->get('claroline.resource.manager');
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $user = $em->getRepository('ClarolineCoreBundle:User')->findOneBy(array('username' => $this->username));
        $root = $em->getRepository('Claroline\CoreBundle\Entity\Resource\AbstractResource')
            ->findOneBy(array('parent' => null, 'workspace' => $user->getPersonalWorkspace()->getId()));

        $collaborators = $em->getRepository('ClarolineCoreBundle:User')->getUsersOfWorkspace($user->getPersonalWorkspace());
        $maxOffset = count($collaborators);
        $this->log("collaborators found: ".count($collaborators));
        $maxOffset--;
        $forum = new Forum();
        $forum->setName($this->forumName);
        $forum = $creator->create($forum, $root->getId(), 'claroline_forum', null, $user);
        $this->log("forum {$forum->getName()} created");

        for ($i = 0; $i < $this->nbSubjects; $i++) {
            $title = $this->container->get('claroline.utilities.lipsum_generator')->generateLipsum(5);
            $user = $collaborators[rand(0, $maxOffset)];
            $subject = new Subject();
            $subject->setName($title);
            $subject->setTitle($title);
            $subject->setCreator($user);
            $this->log("subject $title created");
            $subject = $creator->create($subject, $forum->getId(), 'claroline_subject', null, $user);

            $entityToBeDetached = array();
            for ($j=0; $j<$this->nbMessages; $j++){

                $sender = $collaborators[rand(0, $maxOffset)];
                $message = new Message();
                $message->setName('tmp-'.microtime());
                $message->setCreator($sender);
                $message->setContent($this->container->get('claroline.utilities.lipsum_generator')->generateLipsum(150, true));
                $inst = $creator->create($message, $subject->getId(), 'claroline_message', null, $sender);
                $entityToBeDetached[] = $message;
                $entityToBeDetached[] = $inst;
            }
            $manager->flush();
//            foreach ($entityToBeDetached as $msg) {
//                $manager->detach($msg);
//            }
        }

        $manager->flush();

        $this->addReference("forum_instance/forum", $forum);
    }
}