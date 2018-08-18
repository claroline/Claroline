<?php

namespace Claroline\MessageBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\MessageBundle\Entity\Message;
use Claroline\MessageBundle\Entity\UserMessage;
use Claroline\MessageBundle\Manager\MessageManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @DI\Service("claroline.serializer.messaging_message")
 * @DI\Tag("claroline.serializer")
 */
class MessageSerializer
{
    use SerializerTrait;

    /**
     * ParametersSerializer constructor.
     *
     * @DI\InjectParams({
     *     "om"             = @DI\Inject("claroline.persistence.object_manager"),
     *     "tokenStorage"   = @DI\Inject("security.token_storage"),
     *     "manager"        = @DI\Inject("claroline.manager.message_manager"),
     *     "userSerializer" = @DI\Inject("claroline.serializer.user"),
     * })
     *
     * @param SerializerProvider $serializer
     */
    public function __construct(
        ObjectManager $om,
        MessageManager $manager,
        TokenStorageInterface $tokenStorage,
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
     * @return string
     */
    /*
    public function getSchema()
    {
       return '#/plugin/message/message.json';
    }*/

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

        //mainly for tests or if someting went wrong
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
          ],
        ];

        $data['from'] = $message->getSender() ?
            $this->userSerializer->serialize($message->getSender(), [Options::SERIALIZE_MINIMAL]) :
            ['username' => $message->getSenderUsername()];

        if (in_array(Options::IS_RECURSIVE, $options)) {
            $data['children'] = array_map(function (Message $child) {
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
     * @return Plugin
     */
    public function deserialize($data, Message $message, array $options = [])
    {
        $userMessages = $this->getUserMessages($message);

        $this->sipe('object', 'setObject', $data, $message);
        $this->sipe('content', 'setContent', $data, $message);
        $this->sipe('to', 'setTo', $data, $message);
        $currentUser = $this->tokenStorage->getToken()->getUser();

        if (isset($data['parent'])) {
            $parent = $this->om->getRepository(Message::class)->find($data['parent']['id']);
            $message->setParent($parent);
        }

        if ($currentUser instanceof User) {
            $message->setSender($currentUser);
        }

        //when we create the message, the userMessage doesn't exist yet
        if (isset($data['meta'])) {
            foreach ($userMessages as $userMessage) {
                if (isset($data['meta']['removed'])) {
                    $userMessage->setIsRemoved($data['meta']['removed']);
                }

                if (isset($data['meta']['read'])) {
                    $userMessage->setIsRead($data['meta']['read']);
                }

                $this->om->persist($userMessage);
            }
        }

        return $message;
    }

    //we return an array for backward compatibity. It used to create many messages for a single user.
    private function getUserMessages(Message $message)
    {
        $currentUser = $this->tokenStorage->getToken()->getUser();

        return $this->om->getRepository(UserMessage::class)->findBy(['message' => $message, 'user' => $currentUser]);
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
