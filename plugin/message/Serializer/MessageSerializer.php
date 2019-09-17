<?php

namespace Claroline\MessageBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
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

    /**
     * ParametersSerializer constructor.
     *
     * @param ObjectManager         $om
     * @param MessageManager        $manager
     * @param TokenStorageInterface $tokenStorage
     * @param UserSerializer        $userSerializer
     */
    public function __construct(
        ObjectManager $om,
        TokenStorageInterface $tokenStorage,
        MessageManager $manager,
        UserSerializer $userSerializer
    ) {
        $this->om = $om;
        $this->tokenStorage = $tokenStorage;
        $this->manager = $manager;
        $this->userSerializer = $userSerializer;
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
          'to' => $message->getTo(),
          'meta' => [
            'date' => DateNormalizer::normalize($message->getDate()),
            'read' => $userMessage->isRead(),
            'removed' => $userMessage->isRemoved(),
            'sent' => $userMessage->isSent(),
            'umuuid' => $userMessage->getUuid(),
          ],
        ];

        $data['from'] = $message->getSender() ?
            $this->userSerializer->serialize($message->getSender(), [Options::SERIALIZE_MINIMAL]) :
            ['username' => $message->getSenderUsername()];

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

        //build the "to" string here
        //; is the separator
        //{} for groups
        //[] for workspaces
        $receivers = [];

        if (isset($data['toGroups'])) {
            $receivers = array_merge(array_map(function ($group) {
                return '{'.$group['name'].'}';
            }, $data['toGroups']), $receivers);
        }

        if (isset($data['toUsers'])) {
            $receivers = array_merge(array_map(function ($user) {
                return $user['username'];
            }, $data['toUsers']), $receivers);
        }

        if (isset($data['toWorkspaces'])) {
            $receivers = array_merge(array_map(function ($workspace) {
                return '['.$workspace['code'].']';
            }, $data['toWorkspaces']), $receivers);
        }

        $receiversString = implode(';', $receivers);

        if ('' !== $receiversString && !$message->getTo()) {
            $message->setTo($receiversString);
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
