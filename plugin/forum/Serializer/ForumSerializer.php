<?php

namespace Claroline\ForumBundle\Serializer;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\ForumBundle\Entity\Forum;
use Claroline\ForumBundle\Entity\Validation\User;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
     *     "container" = @DI\Inject("service_container")
     * })
     *
     * @param FinderProvider $finder
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
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
        $finder = $this->container->get('claroline.api.finder');
        $currentUser = $this->container->get('security.token_storage')->getToken()->getUser();

        if (!is_string($currentUser)) {
            $forumUser = $this->container->get('claroline.manager.forum_manager')->getValidationUser(
                $currentUser,
                $forum
            );
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
              'users' => $finder->fetch('Claroline\ForumBundle\Entity\Validation\User', ['forum' => $forum->getUuid()], null, 0, 0, true),
              'subjects' => $finder->fetch('Claroline\ForumBundle\Entity\Subject', ['forum' => $forum->getUuid()], null, 0, 0, true),
              'messages' => $finder->fetch('Claroline\ForumBundle\Entity\Message', ['forum' => $forum->getUuid(), 'moderation' => Forum::VALIDATE_NONE], null, 0, 0, true),
              'myMessages' => !is_string($currentUser) ?
                  $finder->fetch('Claroline\ForumBundle\Entity\Message', ['forum' => $forum->getUuid(), 'creator' => $currentUser->getUsername()], null, 0, 0, true) :
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
                'class' => 'Claroline\ForumBundle\Entity\Subject',
                'ids' => [$subject->getUuid()],
            ]);

            $this->container->get('event_dispatcher')->dispatch(
                'claroline_retrieve_used_tags_by_class_and_ids',
                $event
            );

            $tags = $event->getResponse();
            $availables = array_merge($availables, $tags);
        }

        return $availables;
    }
}
