<?php

namespace Claroline\AnnouncementBundle\Serializer;

use Claroline\AnnouncementBundle\Entity\Announcement;
use Claroline\AnnouncementBundle\Entity\AnnouncementAggregate;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\File\PublicFileSerializer;
use Claroline\CoreBundle\API\Serializer\Resource\ResourceNodeSerializer;
use Claroline\CoreBundle\API\Serializer\User\RoleSerializer;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\API\Serializer\Workspace\WorkspaceSerializer;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\CoreBundle\Library\Normalizer\DateRangeNormalizer;
use Claroline\CoreBundle\Library\Utilities\FileUtilities;
use Claroline\CoreBundle\Repository\User\RoleRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AnnouncementSerializer
{
    use SerializerTrait;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var UserSerializer */
    private $userSerializer;

    /** @var PublicFileSerializer */
    private $publicFileSerializer;

    /** @var ObjectManager */
    private $om;

    private $aggregateRepo;
    /** @var RoleRepository */
    private $roleRepo;

    /** @var FileUtilities */
    private $fileUt;

    /** @var WorkspaceSerializer */
    private $wsSerializer;

    /** @var ResourceNodeSerializer */
    private $nodeSerializer;

    /** @var RoleSerializer */
    private $roleSerializer;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        UserSerializer $userSerializer,
        ObjectManager $om,
        WorkspaceSerializer $wsSerializer,
        ResourceNodeSerializer $nodeSerializer,
        PublicFileSerializer $publicFileSerializer,
        FileUtilities $fileUt,
        RoleSerializer $roleSerializer
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->userSerializer = $userSerializer;
        $this->om = $om;
        $this->fileUt = $fileUt;
        $this->wsSerializer = $wsSerializer;
        $this->nodeSerializer = $nodeSerializer;
        $this->publicFileSerializer = $publicFileSerializer;
        $this->roleSerializer = $roleSerializer;

        $this->aggregateRepo = $om->getRepository('ClarolineAnnouncementBundle:AnnouncementAggregate');
        $this->roleRepo = $om->getRepository('ClarolineCoreBundle:Role');
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
        $poster = null;
        if ($announce->getPoster()) {
            /** @var PublicFile $poster */
            $poster = $this->om->getRepository(PublicFile::class)->findOneBy([
                'url' => $announce->getPoster(),
            ]);
        }

        return [
            'id' => $announce->getUuid(),
            'title' => $announce->getTitle(),
            'content' => $announce->getContent(),
            'workspace' => $announce->getAggregate()->getResourceNode()->getWorkspace() ?
                $this->wsSerializer->serialize($announce->getAggregate()->getResourceNode()->getWorkspace(), [Options::SERIALIZE_MINIMAL]) :
                null, // TODO : remove me, can be retrieved from the node
            'meta' => [
                // required to be able to open the announce from the data source
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
            'poster' => $poster ? $this->publicFileSerializer->serialize($poster) : null,
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

        // set aggregate
        if (isset($data['aggregate']['id'])) {
            /** @var AnnouncementAggregate $aggregate */
            $aggregate = $this->aggregateRepo->findOneBy(['uuid' => $data['aggregate']['id']]);

            if (!empty($aggregate)) {
                $announce->setAggregate($aggregate);
            }
        }

        // set roles
        $announce->emptyRoles();

        if (!empty($data['roles'])) {
            foreach ($data['roles'] as $roleData) {
                /** @var Role $role */
                $role = $this->roleRepo->findOneBy(['uuid' => $roleData['id']]);

                if (!empty($role)) {
                    $announce->addRole($role);
                }
            }
        }

        if (isset($data['poster']) && isset($data['poster']['id'])) {
            $publicFile = $this->om->getRepository(PublicFile::class)->find($data['poster']['id']);
            if ($publicFile) {
                $poster = $this->publicFileSerializer->deserialize(
                    $data['poster'],
                    $publicFile
                );
                $announce->setPoster($data['poster']['url']);
                $this->fileUt->createFileUse(
                    $poster,
                    Announcement::class,
                    $announce->getUuid()
                );
            }
        }

        return $announce;
    }
}
