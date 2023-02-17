<?php

namespace Claroline\ForumBundle\Serializer;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\ForumBundle\Entity\Forum;
use Claroline\ForumBundle\Entity\Message;
use Claroline\ForumBundle\Entity\Subject;
use Claroline\ForumBundle\Entity\Validation\User;
use Claroline\ForumBundle\Manager\ForumManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ForumSerializer
{
    use PermissionCheckerTrait;
    use SerializerTrait;

    private $finder;
    private $tokenStorage;
    private $eventDispatcher;
    private $manager;

    public function __construct(
        FinderProvider $finder,
        TokenStorageInterface $tokenStorage,
        EventDispatcherInterface $eventDispatcher,
        ForumManager $manager,
        AuthorizationCheckerInterface $authorization
    ) {
        $this->finder = $finder;
        $this->tokenStorage = $tokenStorage;
        $this->eventDispatcher = $eventDispatcher;
        $this->manager = $manager;
        $this->authorization = $authorization;
    }

    public function getClass(): string
    {
        return Forum::class;
    }

    public function getName(): string
    {
        return 'forum';
    }

    public function getSchema(): string
    {
        return '#/plugin/forum/forum.json';
    }

    public function getSamples(): string
    {
        return '#/plugin/forum/forum';
    }

    public function serialize(Forum $forum, ?array $options = []): array
    {
        $currentUser = $this->tokenStorage->getToken()->getUser();

        if ($currentUser instanceof User) {
            $forumUser = $this->manager->getValidationUser($currentUser, $forum);
        } else {
            $forumUser = new User();
        }

        $now = new \DateTime();
        $readonly = false;

        if ($forum->getLockDate()) {
            $readonly = $forum->getLockDate() > $now;
        }

        $banned = $this->checkPermission('EDIT', $forum->getResourceNode()) ?
          false :
          $forumUser->isBanned() || $readonly;

        return [
            'id' => $forum->getUuid(),
            'moderation' => $forum->getValidationMode(),
            'display' => [
                'description' => $forum->getOverviewMessage(),
                'showOverview' => $forum->getShowOverview(),
                'subjectDataList' => $forum->getDataListOptions(),
                'lastMessagesCount' => $forum->getDisplayMessages(),
                'messageOrder' => $forum->getMessageOrder(),
                'expandComments' => $forum->getExpandComments(),
            ],
            'restrictions' => [
                'lockDate' => DateNormalizer::normalize($forum->getLockDate()),
                'banned' => $banned, // TODO : data about current user should not be here
                'moderator' => $this->checkPermission('EDIT', $forum->getResourceNode()), // TODO : data about current user should not be here
            ],
            'meta' => [
                'tags' => $this->getTags($forum),

                // TODO : do not use finder in serializer
                'users' => $this->finder->fetch(User::class, ['forum' => $forum->getUuid(), 'banned' => false], null, 0, 0, true),
                'subjects' => $this->finder->fetch(Subject::class, ['forum' => $forum->getUuid(), 'flagged' => false], null, 0, 0, true),
                'messages' => $this->finder->fetch(Message::class, ['forum' => $forum->getUuid(), 'flagged' => false], null, 0, 0, true),
                // TODO : data about current user should not be here
                'notified' => $forumUser->isNotified(),
            ],
        ];
    }

    public function deserialize(array $data, Forum $forum, ?array $options = []): Forum
    {
        $this->sipe('moderation', 'setValidationMode', $data, $forum);
        $this->sipe('display.lastMessagesCount', 'setDisplayMessage', $data, $forum);
        $this->sipe('display.subjectDataList', 'setDataListOptions', $data, $forum);
        $this->sipe('display.description', 'setOverviewMessage', $data, $forum);
        $this->sipe('display.showOverview', 'setShowOverview', $data, $forum);
        $this->sipe('display.messageOrder', 'setMessageOrder', $data, $forum);
        $this->sipe('display.expandComments', 'setExpandComments', $data, $forum);

        if (isset($data['restrictions'])) {
            if (isset($data['restrictions']['lockDate'])) {
                $forum->setLockDate(DateNormalizer::denormalize($data['restrictions']['lockDate']));
            }
        }

        return $forum;
    }

    public function getTags(Forum $forum): array
    {
        $subjects = $forum->getSubjects();
        $available = [];

        foreach ($subjects as $subject) {
            $event = new GenericDataEvent([
                'class' => Subject::class,
                'ids' => [$subject->getUuid()],
            ]);

            $this->eventDispatcher->dispatch(
                $event,
                'claroline_retrieve_used_tags_object_by_class_and_ids'
            );

            $tags = $event->getResponse() ?? [];
            $available = array_merge($available, $tags);
        }

        return $available;
    }
}
