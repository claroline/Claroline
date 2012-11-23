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
        $collaborators = $user->getPersonalWorkspace()->getCollaboratorRole()->getUsers();
        $maxOffset = count($collaborators);
        $this->log("collaborators found: ".count($collaborators));
        $maxOffset--;
        $forum = new Forum();
        $forum->setName($this->forumName);
        $forum = $creator->create($forum, $root->getId(), 'claroline_forum', null, $user);
        $this->log("forum {$forum->getName()} created");

        for ($i = 0; $i < $this->nbSubjects; $i++) {
            $title = $this->generateLipsum(5);
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
                $message->setContent($this->generateLipsum(150, true));
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

    private function getArrayLipsum()
    {
        $lipsum = array('lorem', 'ipsum', 'dolor', 'sit', 'amet', 'consectetur', 'adipiscing', 'elit', 'curabitur', 'vel', 'hendrerit', 'libero', 'eleifend',
            'blandit', 'nunc', 'ornare', 'odio', 'ut', 'orci', 'gravida', 'imperdiet', 'nullam', 'purus', 'lacinia', 'a',
            'pretium', 'quis', 'congue', 'praesent', 'sagittis', 'laoreet', 'auctor', 'mauris', 'non', 'velit', 'eros', 'dictum', 'proin', 'accumsan',
            'sapien', 'nec', 'massa', 'volutpat', 'venenatis', 'sed', 'eu', 'molestie', 'lacus', 'quisque', 'porttitor', 'ligula',
            'dui', 'mollis', 'tempus', 'at', 'magna', 'vestibulum', 'turpis', 'ac', 'diam', 'tincidunt', 'id', 'condimentum', 'enim',
            'sodales', 'in', 'hac', 'habitasse', 'platea', 'dictumst', 'aenean', 'neque', 'fusce', 'augue', 'leo', 'eget', 'semper', 'mattis', 'tortor',
            'scelerisque', 'nulla', 'interdum', 'tellus', 'malesuada', 'rhoncus', 'porta', 'sem', 'aliquet', 'et', 'nam', 'suspendisse',
            'potenti', 'vivamus', 'luctus', 'fringilla', 'erat', 'donec', 'justo', 'vehicula', 'ultricies', 'varius', 'ante',
            'primis', 'faucibus', 'ultrices', 'posuere', 'cubilia', 'curae', 'etiam', 'cursus', 'aliquam', 'quam', 'dapibus',
            'nisl', 'feugiat', 'egestas', 'class', 'aptent', 'taciti', 'sociosqu', 'ad', 'litora', 'torquent', 'per', 'conubia', 'nostra',
            'inceptos', 'himenaeos', 'phasellus', 'nibh', 'pulvinar', 'vitae', 'urna', 'iaculis', 'lobortis', 'nisi', 'viverra',
            'arcu', 'morbi', 'pellentesque', 'metus', 'commodo', 'ut', 'facilisis', 'felis', 'tristique', 'ullamcorper', 'placerat', 'aenean',
            'convallis', 'sollicitudin', 'integer', 'rutrum', 'duis', 'est', 'etiam', 'bibendum', 'donec', 'pharetra', 'vulputate', 'maecenas',
            'mi', 'fermentum', 'consequat', 'suscipit', 'aliquam', 'habitant', 'senectus', 'netus', 'fames', 'quisque', 'euismod',
            'curabitur', 'lectus', 'elementum', 'tempor', 'risus', 'cras');

        return $lipsum;
    }

    /**
     * if nbwords = 0, then it's somewhat random (from 5 to 500)
     *
     * @param integer $nbWords
     * @param boolean $isFullText
     *
     * @return string
     */
    private function generateLipsum($nbWords = 0, $isFullText = false)
    {
        $words = $this->getArrayLipsum();
        $content = '';
        $endPhrase = array('?', '!', '.', '...');
        $loopBeforeEnd = 0;

        if ($nbWords == 0) {
            $nbWords = rand(5, 500);
        }

        for ($i = 0; $i < $nbWords; $i++) {

            if ($loopBeforeEnd == 0) {
                $loopBeforeEnd = rand(3, 15);
            }

            $loopBeforeEnd--;

            if ($isFullText && $loopBeforeEnd == 0) {
                $content.="{$endPhrase[array_rand($endPhrase)]} " . ucfirst($words[array_rand($words)]);
            } else {

                if ($content != '') {
                    $content .= ' ';
                }

                $content.= "{$words[array_rand($words)]}";
            }

            $i++;
        }

        if ($isFullText) {
            $content = ucfirst($content) . '.';
        }

        return $content;
    }
}