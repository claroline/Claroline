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
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\CoreBundle\Library\Utilities\FileUtilities;
use Claroline\CoreBundle\Manager\Organization\OrganizationManager;
use Claroline\OpenBadgeBundle\Entity\Assertion;
use Claroline\OpenBadgeBundle\Entity\BadgeClass;
use Claroline\OpenBadgeBundle\Entity\Rules\Rule;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class BadgeClassSerializer
{
    use SerializerTrait;

    private $router;
    private $fileUt;
    private $workspaceSerializer;
    private $userSerializer;
    private $groupSerializer;
    private $om;
    private $organizationManager;
    private $criteriaSerializer;
    private $profileSerializer;
    private $imageSerializer;
    private $eventDispatcher;
    private $tokenStorage;
    private $publicFileSerializer;
    private $organizationSerializer;
    private $ruleSerializer;

    public function __construct(
        FileUtilities $fileUt,
        RouterInterface $router,
        ObjectManager $om,
        OrganizationManager $organizationManager,
        CriteriaSerializer $criteriaSerializer,
        ProfileSerializer $profileSerializer,
        EventDispatcherInterface $eventDispatcher,
        WorkspaceSerializer $workspaceSerializer,
        UserSerializer $userSerializer,
        GroupSerializer $groupSerializer,
        ImageSerializer $imageSerializer,
        TokenStorageInterface $tokenStorage,
        OrganizationSerializer $organizationSerializer,
        PublicFileSerializer $publicFileSerializer,
        RuleSerializer $ruleSerializer
    ) {
        // TODO : simplify DI. There are too many things here
        $this->router = $router;
        $this->fileUt = $fileUt;
        $this->workspaceSerializer = $workspaceSerializer;
        $this->userSerializer = $userSerializer;
        $this->groupSerializer = $groupSerializer;
        $this->om = $om;
        $this->organizationManager = $organizationManager;
        $this->criteriaSerializer = $criteriaSerializer;
        $this->profileSerializer = $profileSerializer;
        $this->imageSerializer = $imageSerializer;
        $this->eventDispatcher = $eventDispatcher;
        $this->tokenStorage = $tokenStorage;
        $this->publicFileSerializer = $publicFileSerializer;
        $this->organizationSerializer = $organizationSerializer;
        $this->ruleSerializer = $ruleSerializer;
    }

    public function getName()
    {
        return 'open_badge_badge';
    }

    /**
     * Serializes a Badge entity.
     *
     * @return array
     */
    public function serialize(BadgeClass $badge, array $options = [])
    {
        $image = null;
        if ($badge->getImage()) {
            /** @var PublicFile $image */
            $image = $this->om->getRepository(PublicFile::class)->findOneBy([
                'url' => $badge->getImage(),
            ]);
        }

        $data = [
            'id' => $badge->getUuid(),
            'name' => $badge->getName(),
            'description' => $badge->getDescription(),
            'color' => $badge->getColor(),
            'criteria' => $badge->getCriteria(),
            'duration' => $badge->getDurationValidation(),
            'image' => $image ? $this->publicFileSerializer->serialize($image) : null,
            'issuer' => $this->organizationSerializer->serialize($badge->getIssuer() ? $badge->getIssuer() : $this->organizationManager->getDefault(true)),
            'tags' => $this->serializeTags($badge),
        ];

        if (in_array(Options::ENFORCE_OPEN_BADGE_JSON, $options)) {
            $data['id'] = $this->router->generate('apiv2_open_badge__badge_class', ['badge' => $badge->getUuid()], UrlGeneratorInterface::ABSOLUTE_URL);
            $data['type'] = 'BadgeClass';
            $data['criteria'] = $this->criteriaSerializer->serialize($badge)['id'];
            $image = $this->om->getRepository(PublicFile::class)->findOneBy(['url' => $badge->getImage()]);

            if ($image) {
                //wtf, this is for mozilla backpack
                $data['image'] = $this->imageSerializer->serialize($image)['id'];
            }

            $data['issuer'] = $badge->getIssuer() ? $this->profileSerializer->serialize($badge->getIssuer()) : null;
        } else {
            $data['issuingMode'] = $badge->getIssuingMode();
            $data['meta'] = [
                'created' => DateNormalizer::normalize($badge->getCreated()),
                'updated' => DateNormalizer::normalize($badge->getUpdated()),
                'enabled' => $badge->getEnabled(),
            ];
            $data['restrictions'] = [
                'hideRecipients' => $badge->getHideRecipients(),
            ];
            $data['permissions'] = $this->serializePermissions($badge);
            $data['rules'] = array_map(function (Rule $rule) {
                return $this->ruleSerializer->serialize($rule);
            }, $badge->getRules()->toArray());
            $data['workspace'] = $badge->getWorkspace() ? $this->workspaceSerializer->serialize($badge->getWorkspace(), [APIOptions::SERIALIZE_MINIMAL]) : null;
            $data['allowedUsers'] = array_map(function (User $user) {
                return $this->userSerializer->serialize($user, [APIOptions::SERIALIZE_MINIMAL]);
            }, $badge->getAllowedIssuers()->toArray());
            $data['allowedGroups'] = array_map(function (Group $group) {
                return $this->groupSerializer->serialize($group, [APIOptions::SERIALIZE_MINIMAL]);
            }, $badge->getAllowedIssuersGroups()->toArray());
        }

        return $data;
    }

    /**
     * Deserializes data into a Group entity.
     *
     * @param array      $data
     * @param BadgeClass $badge
     *
     * @return BadgeClass
     */
    public function deserialize($data, BadgeClass $badge = null, array $options = [])
    {
        $this->sipe('name', 'setName', $data, $badge);
        $this->sipe('description', 'setDescription', $data, $badge);
        $this->sipe('color', 'setColor', $data, $badge);
        $this->sipe('criteria', 'setCriteria', $data, $badge);
        $this->sipe('duration', 'setDurationValidation', $data, $badge);
        $this->sipe('issuingMode', 'setIssuingMode', $data, $badge);
        $this->sipe('meta.enabled', 'setEnabled', $data, $badge);
        $this->sipe('restrictions.hideRecipients', 'setHideRecipients', $data, $badge);

        if (isset($data['issuer'])) {
            /** @var Organization $organization */
            $organization = $this->om->getObject($data['issuer'], Organization::class);
            $badge->setIssuer($organization);
        }

        if (isset($data['image']) && isset($data['image']['id'])) {
            /** @var PublicFile $thumbnail */
            $thumbnail = $this->om->getObject($data['image'], PublicFile::class);
            $badge->setImage($data['image']['url']);
            $this->fileUt->createFileUse(
                $thumbnail,
                BadgeClass::class,
                $badge->getUuid()
            );
        }

        if (isset($data['workspace'])) {
            if (isset($data['workspace']['id'])) {
                /** @var Workspace $workspace */
                $workspace = $this->om->getRepository(Workspace::class)->findOneBy(['uuid' => $data['workspace']['id']]);
                if ($workspace) {
                    $badge->setWorkspace($workspace);
                    // main organization maybe instead ? this is fishy
                    if (count($workspace->getOrganizations()) > 1) {
                        $badge->setIssuer($workspace->getOrganizations()[0]);
                    }
                }
            } else {
                $badge->setWorkspace(null);
            }
        }

        if (isset($data['tags'])) {
            $this->deserializeTags($badge, $data['tags']);
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

        if (isset($data['rules'])) {
            $this->deserializeRules($data['rules'], $badge);
        }

        return $badge;
    }

    private function deserializeRules(array $rules, BadgeClass $badge)
    {
        /** @var Rule[] $existingRules */
        $existingRules = $badge->getRules();

        $ids = [];
        foreach ($rules as $ruleData) {
            $existingRule = null;
            if (isset($ruleData['id'])) {
                foreach ($existingRules as $rule) {
                    if ($rule->getUuid() === $ruleData['id']) {
                        $existingRule = $rule;
                        break;
                    }
                }
            }

            if (empty($existingRule)) {
                $existingRule = new Rule();
            }

            $rule = $this->ruleSerializer->deserialize($ruleData, $existingRule);
            $badge->addRule($rule);

            $ids[] = $rule->getUuid();
        }

        // removes rules which no longer exists
        foreach ($existingRules as $rule) {
            if (!in_array($rule->getUuid(), $ids)) {
                $badge->removeRule($rule);
            }
        }
    }

    private function deserializeTags(BadgeClass $badge, array $tags = [])
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

        $this->eventDispatcher->dispatch($event, 'claroline_tag_multiple_data');
    }

    private function serializeTags(BadgeClass $badge)
    {
        $event = new GenericDataEvent([
            'class' => BadgeClass::class,
            'ids' => [$badge->getUuid()],
        ]);
        $this->eventDispatcher->dispatch($event, 'claroline_retrieve_used_tags_by_class_and_ids');

        return $event->getResponse() ?? [];
    }

    private function serializePermissions(BadgeClass $badge)
    {
        // TODO : move all into BadgeClassVoter

        $currentUser = $this->tokenStorage->getToken()->getUser();
        $issuingModes = $badge->getIssuingMode();

        //we might want to move this logic somewhere else
        $assign = false;
        $isOrganizationManager = false;
        $allowedUserIds = [];

        $roles = $this->tokenStorage->getToken()->getRoleNames();

        //check if user manager of badge organization (issuer)
        $administratedOrganizationsIds = array_map(function (Organization $organization) {
            return $organization->getId();
        }, $currentUser->getAdministratedOrganizations()->toArray());

        if ($badge->getIssuer() && in_array($badge->getIssuer()->getId(), $administratedOrganizationsIds)) {
            $isOrganizationManager = true;
        }
        //check if user in allowed users or groups

        foreach ($issuingModes as $mode) {
            switch ($mode) {
                case BadgeClass::ISSUING_MODE_USER:
                    $allowedUserIds = array_merge(array_map(function (User $user) {
                        return $user->getId();
                    }, $badge->getAllowedIssuers()->toArray(), $allowedUserIds));
                    break;
                case BadgeClass::ISSUING_MODE_GROUP:
                    $users = [];

                    foreach ($badge->getAllowedIssuersGroups() as $group) {
                        foreach ($group->getUsers() as $user) {
                            $users[$user->getId()] = $user;
                        }
                    }

                    $allowedUserIds = array_merge(array_map(function (User $user) {
                        return $user->getId();
                    }, $users, $allowedUserIds));
                    break;
                case BadgeClass::ISSUING_MODE_PEER:
                    //check if current user already has the badge
                    $assertion = $this->om->getRepository(Assertion::class)->findOneBy(['badge' => $badge, 'recipient' => $currentUser]);

                    if ($assertion) {
                        $assign = true;
                    }
                    break;
                case BadgeClass::ISSUING_MODE_WORKSPACE:
                    $workspace = $badge->getWorkspace();
                    if ($workspace) {
                        $managerRole = $workspace->getManagerRole();
                        if (in_array($managerRole, $roles)) {
                            $assign = true;
                        }
                    }
                    break;
            }
        }

        if (in_array($currentUser->getId(), $allowedUserIds)) {
            $assign = true;
        }

        $assign = $assign | $isOrganizationManager;
        $isAdmin = false;
        //check administrator status here

        foreach ($this->tokenStorage->getToken()->getRoleNames() as $role) {
            if ('ROLE_ADMIN' === $role) {
                $isAdmin = true;
            }
        }

        $workspace = $badge->getWorkspace();
        if ($workspace) {
            $managerRole = $workspace->getManagerRole();
            if (in_array($managerRole, $roles)) {
                $isAdmin = true;
            }
        }

        return [
          'assign' => (bool) ($assign | $isAdmin),
          'edit' => (bool) ($isOrganizationManager | $isAdmin),
          'delete' => (bool) ($isOrganizationManager | $isAdmin),
        ];
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
