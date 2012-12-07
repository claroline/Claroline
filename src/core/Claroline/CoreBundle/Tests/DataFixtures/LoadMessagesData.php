<?php

namespace Claroline\CoreBundle\Tests\DataFixtures;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Fixtures\LoggableFixture;;
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

    public function __construct($usernames, $nbMessages){
        $this->usernames = $usernames;
        $this->nbMessages = $nbMessages;
    }

    public function load(ObjectManager $manager)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $userRepository = $em->getRepository('Claroline\CoreBundle\Entity\User');
        $users = $userRepository->findAll();
        $count = count($users);
        $count--;

        for ($i=0; $i<$this->nbMessages; $i++){
            $message = new Message;
            $message->setObject($this->container->get('claroline.utilities.lipsum_generator')->generateLipsum(5));
            $message->setContent($this->container->get('claroline.utilities.lipsum_generator')->generateLipsum(150, true));

            $userMessage = new UserMessage();
            $userMessage->setMessage($message);


            if (isset($this->usernames['from'])){
                $message->setUser($userRepository->findOneBy(array('username' => $this->usernames['from'])));
            } else {
                $message->setUser($users[rand(0, $count)]);
            }

            if (isset($this->usernames['to'])){
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
