<?php

namespace Claroline\CoreBundle\API\Serializer\User;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\Facet\FieldFacetValueSerializer;
use Claroline\CoreBundle\API\Serializer\File\PublicFileSerializer;
use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Claroline\CoreBundle\Entity\Facet\FieldFacetValue;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceRegistrationQueue;
use Claroline\CoreBundle\Event\User\DecorateUserEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Normalizer\DateRangeNormalizer;
use Claroline\CoreBundle\Repository\Facet\FieldFacetRepository;
use Claroline\CoreBundle\Repository\Facet\FieldFacetValueRepository;
use Claroline\CoreBundle\Repository\User\RoleRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class UserSerializer
{
    use SerializerTrait;

    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var ObjectManager */
    protected $om;
    /** @var PlatformConfigurationHandler */
    private $config;
    /** @var PublicFileSerializer */
    private $fileSerializer;
    /** @var OrganizationSerializer */
    private $organizationSerializer;
    /** @var FieldFacetValueSerializer */
    private $fieldFacetValueSerializer;
    /** @var ContainerInterface */
    private $container;
    /** @var StrictDispatcher */
    private $eventDispatcher;

    private $organizationRepo;
    /** @var RoleRepository */
    private $roleRepo;
    /** @var FieldFacetRepository */
    private $fieldFacetRepo;
    /** @var FieldFacetValueRepository */
    private $fieldFacetValueRepo;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorization,
        ObjectManager $om,
        PlatformConfigurationHandler $config,
        PublicFileSerializer $fileSerializer,
        ContainerInterface $container,
        StrictDispatcher $eventDispatcher,
        OrganizationSerializer $organizationSerializer,
        FieldFacetValueSerializer $fieldFacetValueSerializer
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authorization = $authorization;
        $this->om = $om;
        $this->config = $config;
        $this->fileSerializer = $fileSerializer;
        $this->container = $container;
        $this->eventDispatcher = $eventDispatcher;
        $this->organizationSerializer = $organizationSerializer;
        $this->fieldFacetValueSerializer = $fieldFacetValueSerializer;

        $this->organizationRepo = $om->getRepository(Organization::class);
        $this->roleRepo = $om->getRepository(Role::class);
        $this->fieldFacetRepo = $om->getRepository(FieldFacet::class);
        $this->fieldFacetValueRepo = $om->getRepository(FieldFacetValue::class);
    }

    public function getName(): string
    {
        return 'user';
    }

    public function getClass(): string
    {
        return User::class;
    }

    public function getSchema(): string
    {
        return '#/main/core/user.json';
    }

    public function getSamples(): string
    {
        return '#/main/core/user';
    }

    public function serialize(User $user, array $options = []): array
    {
        $token = $this->tokenStorage->getToken();

        $showEmailRoles = $this->config->getParameter('profile.show_email') ?? [];
        $showEmail = false;
        if ($token) {
            $isOwner = $token->getUser() instanceof User && $token->getUser()->getUuid() === $user->getUuid();
            $showEmail = $isOwner || !empty(array_filter($token->getRoleNames(), function (string $role) use ($showEmailRoles) {
                return 'ROLE_ADMIN' === $role || in_array($role, $showEmailRoles);
            }));
        }

        $serializedUser = [
            'autoId' => $user->getId(),
            'id' => $user->getUuid(),
            'name' => $user->getFullName(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'username' => $user->getUsername(),
            'picture' => $this->serializePicture($user),
            'thumbnail' => $this->serializeThumbnail($user),
            'email' => $showEmail ? $user->getEmail() : null,
            'administrativeCode' => $user->getAdministrativeCode(),
            'phone' => $showEmail ? $user->getPhone() : null,
            'meta' => $this->serializeMeta($user),
            'publicUrl' => $user->getPublicUrl(), // todo : merge with the one from meta (I do it to have it in minimal)
            'permissions' => $this->serializePermissions($user),
            'restrictions' => $this->serializeRestrictions($user),
        ];

        if (!in_array(Options::SERIALIZE_MINIMAL, $options)) {
            $userRoles = array_map(function (Role $role) { // todo use role serializer with minimal option
                return [
                    'id' => $role->getUuid(),
                    'type' => $role->getType(),
                    'name' => $role->getName(),
                    'translationKey' => $role->getTranslationKey(),
                    'workspace' => $role->getWorkspace() ? ['id' => $role->getWorkspace()->getUuid()] : null,
                    'context' => 'user',
                ];
            }, $user->getEntityRoles(false));
            $groupRoles = array_map(function (Role $role) { // todo use role serializer with minimal option
                return [
                    'id' => $role->getUuid(),
                    'type' => $role->getType(),
                    'name' => $role->getName(),
                    'translationKey' => $role->getTranslationKey(),
                    'workspace' => $role->getWorkspace() ? ['id' => $role->getWorkspace()->getUuid()] : null,
                    'context' => 'group',
                ];
            }, $user->getGroupRoles());

            $serializedUser = array_merge($serializedUser, [
                'poster' => $this->serializePoster($user),
                'roles' => array_merge($userRoles, $groupRoles),
                'groups' => array_values(array_map(function (Group $group) { // todo use group serializer with minimal option
                    return [
                        'id' => $group->getUuid(),
                        'name' => $group->getName(),
                    ];
                }, $user->getGroups()->toArray())),
            ]);

            if ($user->getMainOrganization()) {
                $serializedUser['mainOrganization'] = $this->organizationSerializer->serialize($user->getMainOrganization());
            }

            // TODO : do not get it here
            $serializedUser['administratedOrganizations'] = array_map(function ($organization) {
                return $this->organizationSerializer->serialize($organization);
            }, $user->getAdministratedOrganizations()->toArray());
        }

        if (in_array(Options::SERIALIZE_FACET, $options)) {
            $fields = $this->fieldFacetValueRepo->findPlatformValuesByUser($user);
            if (!empty($fields)) {
                $serializedUser['profile'] = [];
                foreach ($fields as $field) {
                    // we just flatten field facets in the base user structure
                    $serializedUser['profile'][$field->getFieldFacet()->getUuid()] = $field->getValue();
                }
            }
        }

        return $this->decorate($user, $serializedUser);
    }

    /**
     * Dispatches an event to let plugins add some custom data to the serialized user.
     * For example: AuthenticationBundle adds CAS Id to the serialized user.
     */
    private function decorate(User $user, array $serializedUser): array
    {
        $unauthorizedKeys = array_keys($serializedUser);

        /** @var DecorateUserEvent $event */
        $event = $this->eventDispatcher->dispatch('serialize_user', 'User\DecorateUser', [
            $user,
            $unauthorizedKeys,
        ]);

        return array_merge(
            $serializedUser,
            $event->getInjectedData()
        );
    }

    /**
     * Serialize the user picture.
     *
     * @return array|null
     */
    private function serializePicture(User $user)
    {
        if (!empty($user->getPicture())) {
            /** @var PublicFile $file */
            $file = $this->om
                ->getRepository('Claroline\CoreBundle\Entity\File\PublicFile')
                ->findOneBy(['url' => $user->getPicture()]);

            if ($file) {
                return $this->fileSerializer->serialize($file);
            }
        }

        return null;
    }

    /**
     * Serialize the user poster.
     *
     * @return array|null
     */
    private function serializePoster(User $user)
    {
        if (!empty($user->getPoster())) {
            /** @var PublicFile $file */
            $file = $this->om
                ->getRepository(PublicFile::class)
                ->findOneBy(['url' => $user->getPoster()]);

            if ($file) {
                return $this->fileSerializer->serialize($file);
            }
        }

        return null;
    }

    /**
     * Serialize the user thumbnail.
     *
     * @return array|null
     */
    private function serializeThumbnail(User $user)
    {
        if (!empty($user->getThumbnail())) {
            /** @var PublicFile $file */
            $file = $this->om
                ->getRepository(PublicFile::class)
                ->findOneBy(['url' => $user->getThumbnail()]);

            if ($file) {
                return $this->fileSerializer->serialize($file);
            }
        }

        return null;
    }

    private function serializeMeta(User $user): array
    {
        $locale = $user->getLocale();
        if (empty($locale)) {
            $locale = $this->config->getParameter('locale_language');
        }

        return [
            'publicUrl' => $user->getPublicUrl(),
            'publicUrlTuned' => $user->hasTunedPublicUrl(),
            'acceptedTerms' => $user->hasAcceptedTerms(),
            'lastLogin' => $user->getLastLogin() ? $user->getLastLogin()->format('Y-m-d\TH:i:s') : null,
            'created' => $user->getCreated() ? $user->getCreated()->format('Y-m-d\TH:i:s') : null,
            'description' => $user->getDescription(),
            'mailValidated' => $user->isMailValidated(),
            'mailNotified' => $user->isMailNotified(),
            'authentication' => $user->getAuthentication(),
            'personalWorkspace' => (bool) $user->getPersonalWorkspace(),
            'locale' => $locale,
        ];
    }

    private function deserializeMeta(array $meta, User $user)
    {
        $this->sipe('mailNotified', 'setIsMailNotified', $meta, $user);
        $this->sipe('description', 'setDescription', $meta, $user);
        $this->sipe('mailValidated', 'setIsMailValidated', $meta, $user);

        if (empty($meta) || empty($meta['locale'])) {
            if (empty($user->getLocale())) {
                // set default
                $user->setLocale($this->config->getParameter('locale_language'));
            }
        } else {
            // use given locale
            $user->setLocale($meta['locale']);
        }

        // tune public URL
        if (!empty($meta['publicUrl']) && $meta['publicUrl'] !== $user->getPublicUrl()) {
            $user->setPublicUrl($meta['publicUrl']);
            $user->setHasTunedPublicUrl(true);
        }
    }

    private function serializePermissions(User $user): array
    {
        $token = $this->tokenStorage->getToken();
        $currentUser = $token ? $token->getUser() : null;

        $isOwner = $currentUser instanceof User && $currentUser->getUuid() === $user->getUuid();

        return [
            'open' => true,
            'contact' => !$isOwner,
            'edit' => $this->authorization->isGranted('EDIT', $user),
            'administrate' => $this->authorization->isGranted('ADMINISTRATE', $user),
            'delete' => $this->authorization->isGranted('DELETE', $user),
        ];
    }

    private function serializeRestrictions(User $user): array
    {
        return [
            'locked' => $user->isLocked(),
            'disabled' => !$user->isEnabled(),
            'removed' => $user->isRemoved(),
            'dates' => DateRangeNormalizer::normalize($user->getInitDate(), $user->getExpirationDate()),
        ];
    }

    private function deserializeRestrictions(array $restrictions, User $user)
    {
        if (isset($restrictions['disabled'])) {
            $user->setIsEnabled(!$restrictions['disabled']);
        }

        if (isset($restrictions['locked'])) {
            $user->setLocked($restrictions['locked']);
        }

        if (isset($restrictions['removed'])) {
            $user->setRemoved($restrictions['removed']);
        }

        if (isset($restrictions['dates'])) {
            $dateRange = DateRangeNormalizer::denormalize($restrictions['dates']);

            $user->setInitDate($dateRange[0]);
            $user->setExpirationDate($dateRange[1]);
        }
    }

    public function deserialize(array $data, User $user = null, array $options = []): User
    {
        if (empty($user)) {
            $user = new User();
        }

        $this->sipe('id', 'setUuid', $data, $user);
        $this->sipe('picture.url', 'setPicture', $data, $user);
        $this->sipe('username', 'setUserName', $data, $user);
        $this->sipe('firstName', 'setFirstName', $data, $user);
        $this->sipe('lastName', 'setLastName', $data, $user);
        $this->sipe('email', 'setEmail', $data, $user);
        $this->sipe('code', 'setCode', $data, $user);
        $this->sipe('locale', 'setLocale', $data, $user);
        $this->sipe('phone', 'setPhone', $data, $user);
        $this->sipe('administrativeCode', 'setAdministrativeCode', $data, $user);

        //don't trim the password just in case
        $this->sipe('plainPassword', 'setPlainPassword', $data, $user, false);

        if (isset($data['meta'])) {
            $this->deserializeMeta($data['meta'], $user);
        }

        if (isset($data['poster']) && isset($data['poster']['url'])) {
            $user->setPoster($data['poster']['url']);
        }

        if (isset($data['thumbnail']) && isset($data['thumbnail']['url'])) {
            $user->setThumbnail($data['thumbnail']['url']);
        }

        if (isset($data['restrictions'])) {
            $this->deserializeRestrictions($data['restrictions'], $user);
        }

        if (isset($data['mainOrganization'])) {
            /** @var Organization $organization */
            $organization = $this->om->getObject($data['mainOrganization'], Organization::class, ['id', 'code', 'name', 'email']);

            if ($organization) {
                $user->setMainOrganization($organization);
            }
        }

        //only add role here. If we want to remove them, use the crud remove method instead
        //it's useful if we want to create a user with a list of roles
        if (isset($data['roles'])) {
            foreach ($data['roles'] as $roleData) {
                /** @var Role|null $role */
                $role = null;

                if (isset($roleData['id'])) {
                    $role = $this->roleRepo->findOneBy(['uuid' => $roleData['id']]);
                } elseif (isset($roleData['name'])) {
                    $role = $this->roleRepo->findOneBy(['name' => $roleData['name']]);
                } elseif (isset($roleData['translationKey'])) {
                    $role = $this->roleRepo->findOneBy([
                        'translationKey' => $roleData['translationKey'],
                        'type' => Role::PLATFORM_ROLE,
                    ]);
                }

                // TODO : this should not be done here
                if ($role && $role->getId()) {
                    $roleWs = $role->getWorkspace();
                    if (in_array(Options::WORKSPACE_VALIDATE_ROLES, $options) && Role::WS_ROLE === $role->getType() && $roleWs->getRegistrationValidation()) {
                        if (!$user->hasRole($role)) {
                            $workspaceManager = $this->container->get('claroline.manager.workspace_manager');

                            if (!$workspaceManager->isUserInValidationQueue($roleWs, $user)) {
                                //for some reason the workspace add manager queue is broken here. Probably it's the log fault
                                $queue = new WorkspaceRegistrationQueue();
                                $queue->setUser($user);
                                $queue->setRole($role);
                                $queue->setWorkspace($roleWs);
                                $this->om->persist($queue);
                            }
                        }
                    } else {
                        $user->addRole($role);
                    }
                }
            }
        }

        //only add groups here. If we want to remove them, use the crud remove method instead
        //it's useful if we want to create a user with a list of roles
        if (isset($data['groups'])) {
            foreach ($data['groups'] as $groupData) {
                /** @var Group $group */
                $group = $this->om->getObject($groupData, Group::class, ['id', 'name']);

                if ($group && $group->getId()) {
                    $user->addGroup($group);
                }
            }
        }

        //only add organizations here. If we want to remove them, use the crud remove method instead
        //it's useful if we want to create a user with a list of roles
        if (isset($data['organizations'])) {
            foreach ($data['organizations'] as $organizationData) {
                $organization = null;

                if (isset($organizationData['id'])) {
                    $organization = $this->organizationRepo->findOneBy(['uuid' => $organizationData['id']]);
                } elseif (isset($organizationData['name'])) {
                    $organization = $this->organizationRepo->findOneBy(['name' => $organizationData['name']]);
                } elseif (isset($organizationData['code'])) {
                    $organization = $this->organizationRepo->findOneBy(['code' => $organizationData['code']]);
                } elseif (isset($organizationData['email'])) {
                    $organization = $this->organizationRepo->findOneBy(['email' => $organizationData['email']]);
                }
                if ($organization && $organization->getId()) {
                    $user->addOrganization($organization);
                }
            }
        }

        if (isset($data['profile'])) {
            $fieldFacets = $this->fieldFacetRepo->findPlatformFieldFacets();
            foreach ($fieldFacets as $fieldFacet) {
                if (isset($data['profile'][$fieldFacet->getUuid()])) {
                    /** @var FieldFacetValue $fieldFacetValue */
                    $fieldFacetValue = $this->fieldFacetValueRepo
                        ->findOneBy([
                            'user' => $user,
                            'fieldFacet' => $fieldFacet,
                        ]) ?? new FieldFacetValue();

                    $user->addFieldFacet(
                        $this->fieldFacetValueSerializer->deserialize([
                            'name' => $fieldFacet->getName(),
                            'value' => $data['profile'][$fieldFacet->getUuid()],
                            'fieldFacet' => ['id' => $fieldFacet->getUuid()],
                        ], $fieldFacetValue)
                    );
                }
            }
        }

        return $user;
    }
}
