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
use Claroline\CoreBundle\Repository\User\GroupRepository;
use Claroline\CoreBundle\Repository\User\UserRepository;
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

    public function getName()
    {
        return 'message';
    }

    public function serialize(Message $message, array $options = []): array
    {
        $userMessage = $this->getUserMessage($message);

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

            $users = $this->userRepo->findByUsernames($receivers['users']);
            $groups = $this->groupRepo->findByNames($receivers['groups']);
            $workspaces = $this->workspaceRepo->findByCodes($receivers['workspaces']);

            $data['receivers'] = [
                'users' => array_map(function (User $user) {
                    return $this->userSerializer->serialize($user, [Options::SERIALIZE_MINIMAL]);
                }, $users),
                'groups' => array_map(function (Group $group) {
                    return $this->groupSerializer->serialize($group, [Options::SERIALIZE_MINIMAL]);
                }, $groups),
                'workspaces' => array_map(function (Workspace $workspace) {
                    return $this->workspaceSerializer->serialize($workspace, [Options::SERIALIZE_MINIMAL]);
                }, $workspaces),
            ];
        }

        if (in_array(Options::IS_RECURSIVE, $options)) {
            $data['children'] = array_map(function (Message $child) use ($options) {
                return $this->serialize($child, $options);
            }, $message->getChildren()->toArray());
        }

        return $data;
    }

    public function deserialize($data, Message $message, array $options = []): Message
    {
        $this->sipe('object', 'setObject', $data, $message);
        $this->sipe('content', 'setContent', $data, $message);

        if (isset($data['parent'])) {
            $parent = $this->om->getRepository(Message::class)->findOneBy(['uuid' => $data['parent']['id']]);
            $message->setParent($parent);
        }

        if (isset($data['sender'])) {
            $sender = $this->om->getRepository(User::class)->findOneBy(['username' => $data['sender']['username']]);
            $message->setSender($sender);
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

        $userMessage = null;
        if ($currentUser instanceof User) {
            $userMessage = $message->getUserMessage($currentUser);
        }

        //mainly for tests or if something went wrong
        if (empty($userMessage)) {
            $userMessage = new UserMessage();
            // a little hacky but if it's not found it's most likely because
            // the message has been ard removed by the current user and we don't want it to pop back
            $userMessage->setRemoved(true);
        }

        return $userMessage;
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
