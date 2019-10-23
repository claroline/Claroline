<?php

namespace Claroline\MessageBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\User\GroupSerializer;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\API\Serializer\Workspace\WorkspaceSerializer;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\CoreBundle\Repository\GroupRepository;
use Claroline\CoreBundle\Repository\UserRepository;
use Claroline\CoreBundle\Repository\WorkspaceRepository;
use Claroline\MessageBundle\Entity\Message;
use Claroline\MessageBundle\Entity\UserMessage;
use Claroline\MessageBundle\Manager\MessageManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class MessageSerializer
{
    use SerializerTrait;

    /** @var ObjectManager */
    private $om;
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var MessageManager */
    private $manager;
    /** @var UserSerializer */
    private $userSerializer;
    /** @var GroupSerializer */
    private $groupSerializer;
    /** @var WorkspaceSerializer */
    private $workspaceSerializer;

    /** @var GroupRepository */
    private $groupRepo;
    /** @var UserRepository */
    private $userRepo;
    /** @var WorkspaceRepository */
    private $workspaceRepo;

    /**
     * ParametersSerializer constructor.
     *
     * @param ObjectManager         $om
     * @param TokenStorageInterface $tokenStorage
     * @param MessageManager        $manager
     * @param UserSerializer        $userSerializer
     * @param GroupSerializer       $groupSerializer
     * @param WorkspaceSerializer   $workspaceSerializer
     */
    public function __construct(
        ObjectManager $om,
        TokenStorageInterface $tokenStorage,
        MessageManager $manager,
        UserSerializer $userSerializer,
        GroupSerializer $groupSerializer,
        WorkspaceSerializer $workspaceSerializer
    ) {
        $this->om = $om;
        $this->tokenStorage = $tokenStorage;
        $this->manager = $manager;

        $this->userSerializer = $userSerializer;
        $this->groupSerializer = $groupSerializer;
        $this->workspaceSerializer = $workspaceSerializer;

        $this->groupRepo = $om->getRepository(Group::class);
        $this->userRepo = $om->getRepository(User::class);
        $this->workspaceRepo = $om->getRepository(Workspace::class);
    }

    public function getClass()
    {
        return Message::class;
    }

    /**
     * Serializes a Message entity.
     *
     * @param Message $message
     * @param array   $options
     *
     * @return array
     */
    public function serialize(Message $message, array $options = [])
    {
        $userMessage = $this->getUserMessage($message);

        //mainly for tests or if something went wrong
        if (!$userMessage) {
            $userMessage = new UserMessage();
        }

        $data = [
            'id' => $message->getUuid(),
            'object' => $message->getObject(),
            'content' => $message->getContent(),
            'from' => $message->getSender() ?
                $this->userSerializer->serialize($message->getSender(), [Options::SERIALIZE_MINIMAL]) :
                ['username' => $message->getSenderUsername()],
            'to' => $message->getTo(),
            'meta' => [
                'date' => DateNormalizer::normalize($message->getDate()),
                'read' => $userMessage->isRead(),
                'removed' => $userMessage->isRemoved(),
                'sent' => $userMessage->isSent(),
                'umuuid' => $userMessage->getUuid(),
            ],
        ];

        // decode to string
        if (!in_array(Options::SERIALIZE_MINIMAL, $options)) {
            $receivers = $message->getReceivers();
            $data['receivers'] = [
                'users' => $this->userRepo->findByUsernames($receivers['users']),
                'groups' => $this->groupRepo->findByNames($receivers['groups']),
                'workspaces' => $this->workspaceRepo->findByCodes($receivers['workspaces']),
            ];
        }

        if (in_array(Options::IS_RECURSIVE, $options)) {
            $data['children'] = array_map(function (Message $child) use ($options) {
                return $this->serialize($child, $options);
            }, $message->getChildren()->toArray());
        }

        return $data;
    }

    /**
     * Deserializes data into a Message entity.
     *
     * @param array   $data
     * @param Message $message
     * @param array   $options
     *
     * @return Message
     */
    public function deserialize($data, Message $message, array $options = [])
    {
        $this->sipe('object', 'setObject', $data, $message);
        $this->sipe('content', 'setContent', $data, $message);
        $currentUser = $this->tokenStorage->getToken()->getUser();

        if (isset($data['parent'])) {
            $parent = $this->om->getRepository(Message::class)->find($data['parent']['id']);
            $message->setParent($parent);
        }

        if ($currentUser instanceof User && in_array(Options::CRUD_CREATE, $options)) {
            $message->setSender($currentUser);
        }

        if (isset($data['receivers'])) {
            $users = isset($data['receivers']['users']) ? $data['receivers']['users'] : [];
            $groups = isset($data['receivers']['groups']) ? $data['receivers']['groups'] : [];
            $workspaces = isset($data['receivers']['workspaces']) ? $data['receivers']['workspaces'] : [];

            $message->setReceivers(
                // users
                array_map(function (array $user) {
                    return $user['username'];
                }, $users),
                // groups
                array_map(function (array $group) {
                    return $group['name'];
                }, $groups),
                // workspaces
                array_map(function (array $workspace) {
                    return $workspace['code'];
                }, $workspaces)
            );
        }

        return $message;
    }

    private function getUserMessage(Message $message)
    {
        $currentUser = $this->tokenStorage->getToken()->getUser();

        return $this->om->getRepository(UserMessage::class)->findOneBy(['message' => $message, 'user' => $currentUser]);
    }

    /**
     * @return string
     */
    public function getSchema()
    {
        return '#/plugin/message/message.json';
    }

    /**
     * @return string
     */
    public function getSamples()
    {
        return '#/plugin/message/message';
    }
}
