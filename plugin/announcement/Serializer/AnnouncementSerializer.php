<?php

namespace Claroline\AnnouncementBundle\Serializer;

use Claroline\AnnouncementBundle\Entity\Announcement;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\API\Serializer\Workspace\WorkspaceSerializer;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Normalizer\DateRangeNormalizer;
use Claroline\CoreBundle\Repository\RoleRepository;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @DI\Service("claroline.serializer.announcement")
 * @DI\Tag("claroline.serializer")
 */
class AnnouncementSerializer
{
    use SerializerTrait;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var UserSerializer */
    private $userSerializer;

    /** @var ObjectManager */
    private $om;

    private $aggregateRepo;
    /** @var RoleRepository */
    private $roleRepo;

    /**
     * AnnouncementSerializer constructor.
     *
     * @DI\InjectParams({
     *     "tokenStorage"   = @DI\Inject("security.token_storage"),
     *     "userSerializer" = @DI\Inject("claroline.serializer.user"),
     *     "om"             = @DI\Inject("claroline.persistence.object_manager"),
     *     "wsSerializer"   = @DI\Inject("claroline.serializer.workspace")
     * })
     *
     * @param TokenStorageInterface $tokenStorage
     * @param UserSerializer        $userSerializer
     * @param ObjectManager         $om
     * @param WorkspaceSerializer   $wsSerializer
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        UserSerializer $userSerializer,
        ObjectManager $om,
        WorkspaceSerializer $wsSerializer
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->userSerializer = $userSerializer;
        $this->om = $om;
        $this->wsSerializer = $wsSerializer;

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
        return [
            'id' => $announce->getUuid(),
            'title' => $announce->getTitle(),
            'content' => $announce->getContent(),
            'workspace' => $announce->getAggregate()->getResourceNode()->getWorkspace() ?
                $this->wsSerializer->serialize($announce->getAggregate()->getResourceNode()->getWorkspace(), [Options::SERIALIZE_MINIMAL]) :
                null,
            'meta' => [
                'resource' => [
                    'id' => $announce->getAggregate()->getResourceNode()->getUuid(),
                ],
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
            'poster' => $announce->getPoster() && $this->om->getRepository(PublicFile::class)->findOneBy([
                  'url' => $announce->getPoster(),
              ]) ? $this->serializer->serialize(
                $this->om->getRepository(PublicFile::class)->findOneBy([
                    'url' => $announce->getPoster(),
              ])
            ) : null,
        ];
    }

    /**
     * @param array        $data
     * @param Announcement $announce
     *
     * @return Announcement
     */
    public function deserialize(array $data, Announcement $announce)
    {
        $announce = $announce ?: new Announcement();

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
            $poster = $this->serializer->deserialize(
                PublicFile::class,
                $data['poster']
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
