<?php

namespace Claroline\AnnouncementBundle\Serializer;

use Claroline\AnnouncementBundle\Entity\Announcement;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CommunityBundle\Serializer\RoleSerializer;
use Claroline\CommunityBundle\Serializer\UserSerializer;
use Claroline\CoreBundle\API\Serializer\Resource\ResourceNodeSerializer;
use Claroline\CoreBundle\API\Serializer\Workspace\WorkspaceSerializer;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\CoreBundle\Library\Normalizer\DateRangeNormalizer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AnnouncementSerializer
{
    use SerializerTrait;

    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ObjectManager $om,
        private readonly UserSerializer $userSerializer,
        private readonly WorkspaceSerializer $wsSerializer,
        private readonly ResourceNodeSerializer $nodeSerializer,
        private readonly RoleSerializer $roleSerializer
    ) {
    }

    public function getClass(): string
    {
        return Announcement::class;
    }

    public function getName(): string
    {
        return 'announcement';
    }

    public function serialize(Announcement $announce): array
    {
        return [
            'id' => $announce->getUuid(),
            'title' => $announce->getTitle(),
            'content' => $announce->getContent(),
            'poster' => $announce->getPoster(),
            'workspace' => $announce->getAggregate()->getResourceNode()->getWorkspace() ?
                $this->wsSerializer->serialize($announce->getAggregate()->getResourceNode()->getWorkspace(), [Options::SERIALIZE_MINIMAL]) :
                null, // used in the announcement DataSource
            'meta' => [
                // required to be able to open the announcement from the data source
                'resource' => $this->nodeSerializer->serialize($announce->getAggregate()->getResourceNode(), [Options::SERIALIZE_MINIMAL]),
                'created' => DateNormalizer::normalize($announce->getCreationDate()),
                'creator' => $announce->getCreator() ? $this->userSerializer->serialize($announce->getCreator(), [Options::SERIALIZE_MINIMAL]) : null,
                'publishedAt' => DateNormalizer::normalize($announce->getPublicationDate()),
                'author' => $announce->getAnnouncer(),
                'notifyUsers' => !empty($announce->getTask()) ? 2 : 0,
                'notificationDate' => !empty($announce->getTask()) ? DateNormalizer::normalize($announce->getTask()->getScheduledDate()) : null,
            ],
            'restrictions' => [
                'hidden' => !$announce->isVisible(),
                'dates' => DateRangeNormalizer::normalize(
                    $announce->getVisibleFrom(),
                    $announce->getVisibleUntil()
                ),
            ],
            'roles' => array_map(function (Role $role) {
                return $this->roleSerializer->serialize($role, [Options::SERIALIZE_MINIMAL]);
            }, $announce->getRoles()),
            'tags' => $this->serializeTags($announce),
        ];
    }

    public function deserialize(array $data, Announcement $announce = null, array $options = []): Announcement
    {
        $announce = $announce ?: new Announcement();

        if (!in_array(Options::REFRESH_UUID, $options)) {
            $this->sipe('id', 'setUuid', $data, $announce);
        } else {
            $announce->refreshUuid();
        }

        $this->sipe('poster', 'setPoster', $data, $announce);

        $announce->setTitle($data['title']);
        $announce->setContent($data['content']);
        $announce->setAnnouncer($data['meta']['author']);

        if (isset($data['meta']) && !empty($data['meta']['creator'])) {
            /** @var User $creator */
            $creator = $this->om->getObject($data['meta']['creator'], User::class);
            $announce->setCreator($creator);
        }

        // calculate visibility restrictions
        $announce->setVisible(!$data['restrictions']['hidden']);

        if (isset($data['restrictions']['dates'])) {
            $dateRange = DateRangeNormalizer::denormalize($data['restrictions']['dates']);

            $announce->setVisibleFrom($dateRange[0]);
            $announce->setVisibleUntil($dateRange[1]);
        }

        // calculate publication date
        if (!$announce->isVisible()) {
            $announce->setPublicationDate(null);
        } elseif (empty($announce->getPublicationDate())) {
            $now = new \DateTime();
            if (empty($announce->getVisibleFrom()) || $announce->getVisibleFrom() < $now) {
                $announce->setPublicationDate($now);
            } else {
                $announce->setPublicationDate($announce->getVisibleFrom());
            }
        }

        // set roles
        $announce->emptyRoles();

        if (!empty($data['roles'])) {
            foreach ($data['roles'] as $roleData) {
                /** @var Role $role */
                $role = $this->om->getObject($roleData, Role::class);
                if (!empty($role)) {
                    $announce->addRole($role);
                }
            }
        }

        if (isset($data['tags'])) {
            $this->deserializeTags($announce, $data['tags'], $options);
        }

        return $announce;
    }

    private function serializeTags(Announcement $announcement): array
    {
        $event = new GenericDataEvent([
            'class' => Announcement::class,
            'ids' => [$announcement->getUuid()],
        ]);
        $this->eventDispatcher->dispatch($event, 'claroline_retrieve_used_tags_by_class_and_ids');

        return $event->getResponse() ?? [];
    }

    private function deserializeTags(Announcement $announcement, array $tags = [], array $options = []): void
    {
        if (in_array(Options::PERSIST_TAG, $options)) {
            $event = new GenericDataEvent([
                'tags' => $tags,
                'data' => [
                    [
                        'class' => Announcement::class,
                        'id' => $announcement->getUuid(),
                        'name' => $announcement->getTitle(),
                    ],
                ],
                'replace' => true,
            ]);

            $this->eventDispatcher->dispatch($event, 'claroline_tag_multiple_data');
        }
    }
}
