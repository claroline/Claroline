<?php

namespace Claroline\CoreBundle\Tests\DataFixtures\Alt;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Fixtures\LoggableFixture;
use Claroline\CoreBundle\Entity\Message;
use Claroline\CoreBundle\Entity\UserMessage;

class LoadMessagesData extends LoggableFixture implements ContainerAwareInterface
{
    /** @var ContainerInterface $container */
    private $container;
    private $nbMessages;
    private $usernames;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Constructor. Expects an associative array. The key must be either 'from' or 'to'
     * and the value is a username. The user must already exists.
     * The value is the number of message sent.

     * @param array $usernames
     * @param int $nbMessages
     */
    public function __construct($usernames, $nbMessages)
    {
        $this->usernames = $usernames;
        $this->nbMessages = $nbMessages;
    }

    public function load(ObjectManager $manager)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $userRepository = $em->getRepository('ClarolineCoreBundle:User');
        $users = $userRepository->findAll();
        $lipusmGenerator = $this->container->get('claroline.utilities.lipsum_generator');
        $count = count($users);
        $count--;

        for ($i = 0; $i < $this->nbMessages; $i++) {
            $message = new Message;
            $message->setObject($lipusmGenerator->generateLipsum(5));
            $message->setContent($lipusmGenerator->generateLipsum(150, true));
            $userMessage = new UserMessage();
            $userMessage->setMessage($message);

            if (isset($this->usernames['from'])) {
                $message->setUser($userRepository->findOneBy(array('username' => $this->usernames['from'])));
            } else {
                $message->setUser($users[rand(0, $count)]);
            }

            if (isset($this->usernames['to'])) {
                $userMessage->setUser($userRepository->findOneBy(array('username' => $this->usernames['to'])));
            } else {
                $userMessage->setUser($users[rand(0, $count)]);
            }

            $manager->persist($userMessage);
            $manager->persist($message);
            $this->addReference("message/message_{$i}", $message);
        }

        $manager->flush();
    }
}
