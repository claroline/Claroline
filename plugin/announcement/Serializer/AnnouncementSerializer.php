<?php

namespace Claroline\AnnouncementBundle\Serializer;

use Claroline\AnnouncementBundle\Entity\Announcement;
use Claroline\AnnouncementBundle\Entity\AnnouncementAggregate;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\File\PublicFileSerializer;
use Claroline\CoreBundle\API\Serializer\Resource\ResourceNodeSerializer;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\API\Serializer\Workspace\WorkspaceSerializer;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Normalizer\DateRangeNormalizer;
use Claroline\CoreBundle\Library\Utilities\FileUtilities;
use Claroline\CoreBundle\Repository\RoleRepository;
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

    /**
     * AnnouncementSerializer constructor.
     *
     * @param TokenStorageInterface  $tokenStorage
     * @param UserSerializer         $userSerializer
     * @param ObjectManager          $om
     * @param WorkspaceSerializer    $wsSerializer
     * @param ResourceNodeSerializer $nodeSerializer
     * @param PublicFileSerializer   $publicFileSerializer
     * @param FileUtilities          $fileUt
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        UserSerializer $userSerializer,
        ObjectManager $om,
        WorkspaceSerializer $wsSerializer,
        ResourceNodeSerializer $nodeSerializer,
        PublicFileSerializer $publicFileSerializer,
        FileUtilities $fileUt
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->userSerializer = $userSerializer;
        $this->om = $om;
        $this->fileUt = $fileUt;
        $this->wsSerializer = $wsSerializer;
        $this->nodeSerializer = $nodeSerializer;
        $this->publicFileSerializer = $publicFileSerializer;

        $this->aggregateRepo = $om->getRepository('ClarolineAnnouncementBundle:AnnouncementAggregate');
        $this->roleRepo = $om->getRepository('ClarolineCoreBundle:Role');
    }

    /**
     * @param Announcement $announce
     *
     * @return array
     */
    public function serialize(Announcement $announce)
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
                'created' => $announce->getCreationDate()->format('Y-m-d\TH:i:s'),
                'creator' => $announce->getCreator() ? $this->userSerializer->serialize($announce->getCreator(), [Options::SERIALIZE_MINIMAL]) : null,
                'publishedAt' => $announce->getPublicationDate() ? $announce->getPublicationDate()->format('Y-m-d\TH:i:s') : null,
                'author' => $announce->getAnnouncer(),
                'notifyUsers' => !empty($announce->getTask()) ? 2 : 0,
                'notificationDate' => !empty($announce->getTask()) ? $announce->getTask()->getScheduledDate()->format('Y-m-d\TH:i:s') : null,
            ],
            'restrictions' => [
                'hidden' => !$announce->isVisible(),
                'dates' => DateRangeNormalizer::normalize(
                    $announce->getVisibleFrom(),
                    $announce->getVisibleUntil()
                ),
            ],
            'roles' => array_map(function (Role $role) {
                return $role->getUuid();
            }, $announce->getRoles()),
            'poster' => $poster ? $this->publicFileSerializer->serialize($poster) : null,
        ];
    }

    /**
     * @param array        $data
     * @param Announcement $announce
     * @param array        $options
     *
     * @return Announcement
     */
    public function deserialize(array $data, Announcement $announce = null, array $options = [])
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

        if (empty($announce->getCreator())) {
            $currentUser = $this->tokenStorage->getToken()->getUser();
            if ($currentUser instanceof User) {
                // only get authenticated user
                $announce->setCreator($currentUser);
            }
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
        } else {
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
            foreach ($data['roles'] as $roleUuid) {
                /** @var Role $role */
                $role = $this->roleRepo->findOneBy(['uuid' => $roleUuid]);

                if (!empty($role)) {
                    $announce->addRole($role);
                }
            }
        }

        if (isset($data['poster']) && isset($data['poster']['id'])) {
            $publicFile = $this->om->getRepository(PublicFile::class)->find($data['poster']['id']);
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

        return $announce;
    }

    public function getClass()
    {
        return Announcement::class;
    }
}
