<?php

namespace Claroline\ChatBundle\Testing;

use Claroline\ChatBundle\Entity\ChatRoom;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @service("claroline.chat_bundle.testing.persister")
 */
class ChatPersister
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var Role
     */
    private $userRole;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @InjectParams({
     *     "om"        = @Inject("claroline.persistence.object_manager"),
     *     "container" = @Inject("service_container")
     * })
     */
    public function __construct(ObjectManager $om, ContainerInterface $container)
    {
        $this->om = $om;
        $this->container = $container;
    }

    public function chatRoom($name, $type, $status, User $owner)
    {
        $resourceType = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneByName('claroline_chat_room');
        $chatRoom = new ChatRoom();
        $chatRoom->setName($name);
        $chatRoom->setRoomName(uniqid());
        $chatRoom->setRoomType($type);
        $chatRoom->setRoomStatus($status);
        $this->om->persist($chatRoom);
        $this->container->get('claroline.manager.resource_manager')->create($chatRoom, $resourceType, $owner);

        return $chatRoom;
    }

    public function persist($entity)
    {
        $this->om->persist($entity);
    }

    public function flush()
    {
        $this->om->flush();
    }
}
