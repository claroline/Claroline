<?php

namespace Claroline\OpenBadgeBundle\Serializer;

use Claroline\AppBundle\API\Options as APIOptions;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\File\PublicFileSerializer;
use Claroline\CoreBundle\API\Serializer\User\GroupSerializer;
use Claroline\CoreBundle\API\Serializer\User\OrganizationSerializer;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\API\Serializer\Workspace\WorkspaceSerializer;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Library\Utilities\FileUtilities;
use Claroline\OpenBadgeBundle\Entity\BadgeClass;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @DI\Service("claroline.serializer.open_badge.badge")
 * @DI\Tag("claroline.serializer")
 */
class BadgeClassSerializer
{
    use SerializerTrait;

    /**
     * @DI\InjectParams({
     *     "fileUt"                 = @DI\Inject("claroline.utilities.file"),
     *     "router"                 = @DI\Inject("router"),
     *     "om"                     = @DI\Inject("claroline.persistence.object_manager"),
     *     "criteriaSerializer"     = @DI\Inject("claroline.serializer.open_badge.criteria"),
     *     "imageSerializer"        = @DI\Inject("claroline.serializer.open_badge.image"),
     *     "eventDispatcher"        = @DI\Inject("event_dispatcher"),
     *     "profileSerializer"      = @DI\Inject("claroline.serializer.open_badge.profile"),
     *     "tokenStorage"           = @DI\Inject("security.token_storage"),
     *     "workspaceSerializer"    = @DI\Inject("claroline.serializer.workspace"),
     *     "userSerializer"         = @DI\Inject("claroline.serializer.user"),
     *     "groupSerializer"        = @DI\Inject("claroline.serializer.group"),
     *     "organizationSerializer" = @DI\Inject("claroline.serializer.organization"),
     *     "publicFileSerializer"   = @DI\Inject("claroline.serializer.public_file")
     * })
     *
     * @param Router $router
     */
    public function __construct(
        FileUtilities $fileUt,
        RouterInterface $router,
        ObjectManager $om,
        CriteriaSerializer $criteriaSerializer,
        ProfileSerializer $profileSerializer,
        EventDispatcherInterface $eventDispatcher,
        WorkspaceSerializer $workspaceSerializer,
        UserSerializer $userSerializer,
        GroupSerializer $groupSerializer,
        ImageSerializer $imageSerializer,
        TokenStorageInterface $tokenStorage,
        OrganizationSerializer $organizationSerializer,
        PublicFileSerializer $publicFileSerializer
    ) {
        $this->router = $router;
        $this->fileUt = $fileUt;
        $this->workspaceSerializer = $workspaceSerializer;
        $this->userSerializer = $userSerializer;
        $this->groupSerializer = $groupSerializer;
        $this->om = $om;
        $this->criteriaSerializer = $criteriaSerializer;
        $this->profileSerializer = $profileSerializer;
        $this->imageSerializer = $imageSerializer;
        $this->eventDispatcher = $eventDispatcher;
        $this->tokenStorage = $tokenStorage;
        $this->publicFileSerializer = $publicFileSerializer;
        $this->organizationSerializer = $organizationSerializer;
    }

    /**
     * Serializes a Group entity.
     *
     * @param Group $group
     * @param array $options
     *
     * @return array
     */
    public function serialize(BadgeClass $badge, array $options = [])
    {
        $data = [
            'id' => $badge->getUuid(),
            'name' => $badge->getName(),
            'description' => $badge->getDescription(),
            'criteria' => $badge->getCriteria(),
            'duration' => $badge->getDurationValidation(),
            'image' => $badge->getImage() && $this->om->getRepository(PublicFile::class)->findOneBy([
                  'url' => $badge->getImage(),
              ]) ? $this->publicFileSerializer->serialize($this->om->getRepository(PublicFile::class)->findOneBy([
                  'url' => $badge->getImage(),
              ])
            ) : null,
            'issuer' => $this->organizationSerializer->serialize($badge->getIssuer()),
            //only in non list mode I guess
            'tags' => $this->serializeTags($badge),
        ];

        if (!in_array(APIOptions::SERIALIZE_LIST, $options)) {
            $data['assignable'] = $this->isAssignable($badge);
        }

        if (in_array(Options::ENFORCE_OPEN_BADGE_JSON, $options)) {
            $data['id'] = $this->router->generate('apiv2_open_badge__badge_class', ['badge' => $badge->getUuid()], UrlGeneratorInterface::ABSOLUTE_URL);
            $data['type'] = 'BadgeClass';
            $data['criteria'] = $this->criteriaSerializer->serialize($badge)['id'];
            $image = $this->om->getRepository(PublicFile::class)->findOneBy(['url' => $badge->getImage()]);

            if ($image) {
                //wtf, this is for mozillabackpack
                $data['image'] = $this->imageSerializer->serialize($image)['id'];
            }

            $data['issuer'] = $this->profileSerializer->serialize($badge->getIssuer());
        } else {
            $data['issuingMode'] = $badge->getIssuingMode();
            $data['meta'] = [
               'created' => $badge->getCreated()->format('Y-m-d\TH:i:s'),
               'updated' => $badge->getUpdated()->format('Y-m-d\TH:i:s'),
               'enabled' => $badge->getEnabled(),
            ];
            $data['workspace'] = $badge->getWorkspace() ? $this->workspaceSerializer->serialize($badge->getWorkspace(), [APIOptions::SERIALIZE_MINIMAL]) : null;
            $data['allowedUsers'] = array_map(function (User $user) {
                return $this->userSerializer->serialize($user);
            }, $badge->getAllowedIssuers()->toArray());
            $data['allowedGroups'] = array_map(function (Group $group) {
                return $this->groupSerializer->serialize($group);
            }, $badge->getAllowedIssuersGroups()->toArray());
        }

        return $data;
    }

    /**
     * Deserializes data into a Group entity.
     *
     * @param \stdClass $data
     * @param Group     $group
     * @param array     $options
     *
     * @return Group
     */
    public function deserialize($data, BadgeClass $badge = null, array $options = [])
    {
        $this->sipe('name', 'setName', $data, $badge);
        $this->sipe('description', 'setDescription', $data, $badge);
        $this->sipe('criteria', 'setCriteria', $data, $badge);
        $this->sipe('duration', 'setDurationValidation', $data, $badge);
        $this->sipe('issuingMode', 'setIssuingMode', $data, $badge);

        if (isset($data['issuer'])) {
            $badge->setIssuer($this->om->getObject($data['issuer'], Organization::class));
        }

        if (isset($data['image']) && isset($data['image']['id'])) {
            $thumbnail = $this->om->getObject($data['image'], PublicFile::class);
            $badge->setImage($data['image']['url']);
            $this->fileUt->createFileUse(
                $thumbnail,
                BadgeClass::class,
                $badge->getUuid()
            );
        }

        if (isset($data['workspace']) && isset($data['workspace']['id'])) {
            $workspace = $this->om->getRepository(Workspace::class)->find($data['workspace']['id']);
            $badge->setWorkspace($workspace);
            //main orga maybe instead ? this is fishy
            $badge->setIssuer($workspace->getOrganizations()[0]);
        }

        if (isset($data['tags'])) {
            if (is_string($data['tags'])) {
                $this->deserializeTags($badge, explode(',', $data['tags']));
            } else {
                $this->deserializeTags($badge, $data['tags']);
            }
        }

        if (isset($data['allowedUsers'])) {
            $allowed = [];
            foreach ($data['allowedUsers'] as $user) {
                $allowed[] = $this->om->getObject($user, User::class);
            }
            $badge->setAllowedIssuers($allowed);
        }

        if (isset($data['allowedGroups'])) {
            $allowed = [];
            foreach ($data['allowedGroups'] as $group) {
                $allowed[] = $this->om->getObject($group, Group::class);
            }
            $badge->setAllowedIssuersGroups($allowed);
        }

        return $badge;
    }

    private function deserializeTags(BadgeClass $badge, array $tags = [], array $options = [])
    {
        $event = new GenericDataEvent([
            'tags' => $tags,
            'data' => [
                [
                    'class' => BadgeClass::class,
                    'id' => $badge->getUuid(),
                    'name' => $badge->getName(),
                ],
            ],
            'replace' => true,
        ]);

        $this->eventDispatcher->dispatch('claroline_tag_multiple_data', $event);
    }

    private function isAssignable(BadgeClass $badge)
    {
        $issuingModes = $badge->getIssuingMode();
        $currentUser = $this->tokenStorage->getToken()->getUser();

        if (!$currentUser instanceof User) {
            return false;
        }

        $roles = array_map(function ($role) {
            return $role->getRole();
        }, $this->tokenStorage->getToken()->getRoles());

        if (in_array('ROLE_ADMIN', $roles)) {
            return true;
        }

        foreach ($issuingModes as $mode) {
            switch ($mode) {
                case BadgeClass::ISSUING_MODE_ORGANIZATION:
                    $organization = $badge->getIssuer();
                    $userOrganizations = $currentUser->getAdministratedOrganizations();
                    foreach ($userOrganizations as $userOrga) {
                        if ($userOrga->getId() === $organization->getId()) {
                            return true;
                        }
                    }
                    break;
                case BadgeClass::ISSUING_MODE_USER:
                    $allowedIssuers = $badge->getAllowedIssuers();
                    foreach ($allowedIssuers as $allowed) {
                        if ($allowed->getId() === $currentUser->getId()) {
                            return true;
                        }
                    }
                    break;
                case BadgeClass::ISSUING_MODE_GROUP:
                    $allowedIssuers = $badge->getAllowedIssuersGroups();
                    foreach ($allowedIssuers as $allowed) {
                        foreach ($currentUser->getGroups() as $group) {
                            if ($group->getId() === $allowed->getId()) {
                                return true;
                            }
                        }
                    }
                    break;
                case BadgeClass::ISSaUING_MODE_PEER:
                    break;
                case BadgeClass::ISSUING_MODE_WORKSPACE:
                    $workspace = $badge->getWorkspace();
                    $managerRole = $workspace->getManagerRole();

                    if (in_array($managerRole, $roles)) {
                        return true;
                    }
                    break;
                case BadgeClass::ISSUING_MODE_AUTO:
                  break;
            }
        }

        return false;
    }

    private function serializeTags(BadgeClass $badge)
    {
        $event = new GenericDataEvent([
            'class' => BadgeClass::class,
            'ids' => [$badge->getUuid()],
        ]);
        $this->eventDispatcher->dispatch('claroline_retrieve_used_tags_by_class_and_ids', $event);

        return $event->getResponse();
    }

    public function getClass()
    {
        return BadgeClass::class;
    }

    /**
     * @return string
     */
    public function getSchema()
    {
        return '#/plugin/open-badge/badge.json';
    }
}
