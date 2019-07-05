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
use Claroline\ForumBundle\Manager\Manager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @DI\Service("claroline.serializer.forum")
 * @DI\Tag("claroline.serializer")
 */
class ForumSerializer
{
    use PermissionCheckerTrait;

    private $finder;

    /**
     * ForumSerializer constructor.
     *
     * @DI\InjectParams({
     *     "finder"          = @DI\Inject("claroline.api.finder"),
     *     "tokenStorage"    = @DI\Inject("security.token_storage"),
     *     "eventDispatcher" = @DI\Inject("event_dispatcher"),
     *     "manager"         = @DI\Inject("claroline.manager.forum_manager")
     * })
     *
     * @param FinderProvider $finder
     */
    public function __construct(
        FinderProvider $finder,
        TokenStorageInterface $tokenStorage,
        Manager $manager,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->finder = $finder;
        $this->tokenStorage = $tokenStorage;
        $this->eventDispatcher = $eventDispatcher;
        $this->manager = $manager;
    }

    use SerializerTrait;

    public function getClass()
    {
        return Forum::class;
    }

    /**
     * @return string
     */
    public function getSchema()
    {
        return '#/plugin/forum/forum.json';
    }

    /**
     * @return string
     */
    public function getSamples()
    {
        return '#/plugin/forum/forum';
    }

    /**
     * Serializes a Forum entity.
     *
     * @param Forum $forum
     * @param array $options
     *
     * @return array
     */
    public function serialize(Forum $forum, array $options = [])
    {
        $currentUser = $this->tokenStorage->getToken()->getUser();

        if (!is_string($currentUser)) {
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
            'maxComment' => $forum->getMaxComment(),
            'display' => [
              'description' => $forum->getDescription(),
              'showOverview' => $forum->getShowOverview(),
              'subjectDataList' => $forum->getDataListOptions(),
              'lastMessagesCount' => $forum->getDisplayMessages(),
            ],
            'restrictions' => [
              'lockDate' => $forum->getLockDate() ? $forum->getLockDate()->format('Y-m-d\TH:i:s') : null, // TODO : use DateNormalizer
              'banned' => $banned, // TODO : data about current user should not be here
              'moderator' => $this->checkPermission('EDIT', $forum->getResourceNode()), // TODO : data about current user should not be here
            ],
            'meta' => [
              'users' => $this->finder->fetch(User::class, ['forum' => $forum->getUuid()], null, 0, 0, true),
              'subjects' => $this->finder->fetch(Subject::class, ['forum' => $forum->getUuid()], null, 0, 0, true),
              //probably an issue with the validate_none somewhere
              'messages' => $this->finder->fetch(Message::class, ['forum' => $forum->getUuid()], null, 0, 0, true),
              'myMessages' => !is_string($currentUser) ?
                  $this->finder->fetch(Message::class, ['forum' => $forum->getUuid(), 'creator' => $currentUser->getUsername()], null, 0, 0, true) :
                  0, // TODO : data about current user should not be here
              'tags' => $this->getTags($forum),
              'notified' => $forumUser->isNotified(),
            ],
        ];
    }

    /**
     * Deserializes data into a Forum entity.
     *
     * @param array $data
     * @param Forum $forum
     * @param array $options
     *
     * @return Forum
     */
    public function deserialize($data, Forum $forum, array $options = [])
    {
        $this->sipe('moderation', 'setValidationMode', $data, $forum);
        $this->sipe('maxComment', 'setMaxComment', $data, $forum);
        $this->sipe('display.lastMessagesCount', 'setDisplayMessage', $data, $forum);
        $this->sipe('display.subjectDataList', 'setDataListOptions', $data, $forum);
        $this->sipe('display.description', 'setDescription', $data, $forum);
        $this->sipe('display.showOverview', 'setShowOverview', $data, $forum);

        if (isset($data['restrictions'])) {
            if (isset($data['restrictions']['lockDate'])) {
                $forum->setLockDate(DateNormalizer::denormalize($data['restrictions']['lockDate']));
            }
        }

        return $forum;
    }

    public function getTags(Forum $forum)
    {
        $subjects = $forum->getSubjects();
        $availables = [];
        //pas terrible comme manière de procéder mais je n'en ai pas d'autre actuellement
        //on va dire que c'est une première version

        foreach ($subjects as $subject) {
            $event = new GenericDataEvent([
                'class' => Subject::class,
                'ids' => [$subject->getUuid()],
            ]);

            $this->eventDispatcher->dispatch(
                'claroline_retrieve_used_tags_object_by_class_and_ids',
                $event
            );

            $tags = $event->getResponse();
            $availables = array_merge($availables, $tags);
        }

        return $availables;
    }
}
