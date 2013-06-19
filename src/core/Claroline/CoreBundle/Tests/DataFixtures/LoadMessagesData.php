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
        foreach ($this->messages as $data) {
            $parent = null;
            if (isset($data['parent'])) {
                $parent = $this->getReference('message/'.$data['parent']);
            }

            $message = $this->container->get('claroline.message.manager')->create(
                $this->getReference('user/' . $data['from']),
                $data['to'],
                $this->container->get('claroline.utilities.lipsum_generator')->generateLipsum(150, true),
                $data['object'],
                $parent
            );

            $this->addReference('message/' . $data['object'], $message);
        }

        $manager->flush();
    }
}
