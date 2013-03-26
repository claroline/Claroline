<?php

namespace Claroline\CoreBundle\Tests\DataFixtures;

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
    private $messages;

    /**
     * Constructor. Expects an array. Each elements of the array is an array whose keys are
     * - ['from'] a user reference (without 'user/')
     * - ['to'] a user reference (without 'user/')
     * - ['object'] the object of the message
     *
     * @param array $messages
     */
    public function __construct(array $messages)
    {
        $this->messages = $messages;
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        $lipusmGenerator = $this->container->get('claroline.utilities.lipsum_generator');

        foreach ($this->messages as $data) {
            $message = new Message;
            $message->setObject($data['object']);
            $message->setContent($lipusmGenerator->generateLipsum(150, true));
            $userMessage = new UserMessage();
            $userMessage->setMessage($message);
            $message->setUser($this->getReference('user/'.$data['from']));
            $message->setSenderUsername($this->getReference('user/'.$data['from'])->getUsername());
            $message->setReceiverUsername($this->getReference('user/'.$data['to'])->getUsername());
            $userMessage->setUser($this->getReference('user/'.$data['to']));
            $senderUserMessage = new UserMessage(true);
            $senderUserMessage->setMessage($message);
            $senderUserMessage->setUser($this->getReference('user/'.$data['from']));
            $manager->persist($userMessage);
            $manager->persist($senderUserMessage);
            $manager->persist($message);
        }

        $manager->flush();
    }
}
