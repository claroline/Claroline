<?php

namespace Claroline\CoreBundle\DataFixtures\Demo;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Fixtures\LoggableFixture;
use Claroline\CoreBundle\Entity\Message;

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
        $messageManager = $this->container->get('claroline.manager.message_manager');
        $generator = $this->container->get('claroline.utilities.lipsum_generator');

        foreach ($this->messages as $data) {
            $message  = new Message();
            $message->setContent($generator->generateLipsum(150, true, 1023));
            $message->setObject($data['object']);
            $message->setTo($data['to']);
            $parent = isset($data['parent']) ?
                $this->getReference('message/' . $data['parent']) :
                null;
            $messageManager->send(
                $this->getReference('user/' . $data['from']),
                $message,
                $parent
            );
            $this->addReference('message/' . $data['object'], $message);
        }

        $manager->flush();
    }
}
