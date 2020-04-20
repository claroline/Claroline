<?php

namespace Claroline\CoreBundle\API\Serializer\User;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\GenericSerializer;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\Facet\FieldFacetValueSerializer;
use Claroline\CoreBundle\API\Serializer\File\PublicFileSerializer;
use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Claroline\CoreBundle\Entity\Facet\FieldFacetValue;
use  Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceRegistrationQueue;
use Claroline\CoreBundle\Event\User\DecorateUserEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Normalizer\DateRangeNormalizer;
use Claroline\CoreBundle\Manager\FacetManager;
use Claroline\CoreBundle\Repository\RoleRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Role\Role as BaseRole;

/**
 * @todo remove parent class
 */
class UserSerializer extends GenericSerializer
{
    use SerializerTrait;

    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var AuthorizationCheckerInterface */
    private $authChecker;
    /** @var ObjectManager */
    protected $om;
    /** @var PlatformConfigurationHandler */
    private $config;
    /** @var FacetManager */
    private $facetManager;
    /** @var PublicFileSerializer */
    private $fileSerializer;
    /** @var ContainerInterface */
    private $container;
    /** @var StrictDispatcher */
    private $eventDispatcher;
    /** @var OrganizationSerializer */
    private $organizationSerializer;
    /** @var FieldFacetValueSerializer */
    private $fieldFacetValueSerializer;

    private $organizationRepo;
    /** @var RoleRepository */
    private $roleRepo;

    /**
     * UserManager constructor.
     *
     * @param TokenStorageInterface         $tokenStorage
     * @param AuthorizationCheckerInterface $authChecker
     * @param ObjectManager                 $om
     * @param PlatformConfigurationHandler  $config
     * @param FacetManager                  $facetManager
     * @param PublicFileSerializer          $fileSerializer
     * @param ContainerInterface            $container
     * @param StrictDispatcher              $eventDispatcher
     * @param OrganizationSerializer        $organizationSerializer
     * @param FieldFacetValueSerializer     $fieldFacetValueSerializer
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authChecker,
        ObjectManager $om,
        PlatformConfigurationHandler $config,
        FacetManager $facetManager,
        PublicFileSerializer $fileSerializer,
        ContainerInterface $container,
        StrictDispatcher $eventDispatcher,
        OrganizationSerializer $organizationSerializer,
        FieldFacetValueSerializer $fieldFacetValueSerializer
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authChecker = $authChecker;
        $this->om = $om;
        $this->config = $config;
        $this->facetManager = $facetManager;
        $this->fileSerializer = $fileSerializer;
        $this->container = $container;
        $this->eventDispatcher = $eventDispatcher;
        $this->organizationSerializer = $organizationSerializer;
        $this->fieldFacetValueSerializer = $fieldFacetValueSerializer;

        $this->organizationRepo = $om->getRepository('ClarolineCoreBundle:Organization\Organization');
        $this->roleRepo = $om->getRepository('ClarolineCoreBundle:Role');
    }

    public function getName()
    {
        return 'user';
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return User::class;
    }

    /**
     * @return string
     */
    public function getSchema()
    {
        return '#/main/core/user.json';
    }

    /**
     * @return string
     */
    public function getSamples()
    {
        return '#/main/core/user';
    }

    /**
     * Serializes a User entity for the JSON api.
     *
     * @param User  $user    - the user to serialize
     * @param array $options
     *
     * @return array - the serialized representation of the user
     */
    public function serialize($user, array $options = [])
    {
        if (isset($options['public']) && $options['public']) {
            // TODO : remove me (only used by BBBPlugin and it's not maintained)
            return $this->serializePublic($user);
        }

        $showEmailRoles = $this->config->getParameter('profile.show_email') ?? [];
        $showEmail = !empty(array_filter($this->tokenStorage->getToken()->getRoles(), function (BaseRole $role) use ($showEmailRoles) {
            return 'ROLE_ADMIN' === $role->getRole() || in_array($role->getRole(), $showEmailRoles);
        }));

        $serializedUser = [
            'autoId' => $user->getId(), //for old compatibility purposes
            'id' => $user->getUuid(),
            'name' => $user->getFirstName().' '.$user->getLastName(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'username' => $user->getUsername(),
            'picture' => $this->serializePicture($user),
            'email' => $showEmail ? $user->getEmail() : null,
            'administrativeCode' => $user->getAdministrativeCode(),
            'phone' => $user->getPhone(),
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
                'meta' => $this->serializeMeta($user),
                'restrictions' => $this->serializeRestrictions($user),
                'roles' => array_merge($userRoles, $groupRoles),
                'groups' => array_map(function (Group $group) { // todo use group serializer with minimal option
                    return [
                        'id' => $group->getUuid(),
                        'name' => $group->getName(),
                    ];
                }, $user->getGroups()->toArray()),
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
            $fields = $this->om
                ->getRepository('Claroline\CoreBundle\Entity\Facet\FieldFacetValue')
                ->findPlatformValuesByUser($user);
            $serializedUser['profile'] = [];

            /** @var FieldFacetValue $field */
            foreach ($fields as $field) {
                // we just flatten field facets in the base user structure
                $serializedUser['profile'][$field->getFieldFacet()->getUuid()] = $field->getValue();
            }
        }

        return $this->decorate($user, $serializedUser);
    }

    /**
     * Dispatches an event to let plugins add some custom data to the serialized user.
     * For example: AuthenticationBundle adds CAS Id to the serialized user.
     *
     * @param User  $user           - the original user entity
     * @param array $serializedUser - the serialized version of the user
     *
     * @return array - the decorated user
     */
    private function decorate(User $user, array $serializedUser)
    {
        $unauthorizedKeys = array_keys($serializedUser);

        /** @var DecorateUserEvent $event */
        $event = $this->eventDispatcher->dispatch(
            'serialize_user',
            'User\DecorateUser',
            [
                $user,
                $unauthorizedKeys,
            ]
        );

        return array_merge(
            $serializedUser,
            $event->getInjectedData()
        );
    }

    /**
     * @param User $user
     *
     * @return array
     */
    private function serializePublic(User $user)
    {
        $settingsProfile = $this->facetManager->getVisiblePublicPreference();
        $publicUser = [];

        foreach ($settingsProfile as $property => $isViewable) {
            if ($isViewable || $user === $this->tokenStorage->getToken()->getUser()) {
                switch ($property) {
                    case 'baseData':
                        $publicUser['lastName'] = $user->getLastName();
                        $publicUser['firstName'] = $user->getFirstName();
                        $publicUser['fullName'] = $user->getFirstName().' '.$user->getLastName();
                        $publicUser['username'] = $user->getUsername();
                        $publicUser['picture'] = $user->getPicture();
                        $publicUser['description'] = $user->getDescription();
                        break;
                    case 'email':
                        $publicUser['mail'] = $user->getEmail();
                        break;
                    case 'phone':
                        $publicUser['phone'] = $user->getPhone();
                        break;
                    case 'sendMail':
                        $publicUser['mail'] = $user->getEmail();
                        $publicUser['allowSendMail'] = true;
                        break;
                    case 'sendMessage':
                        $publicUser['allowSendMessage'] = true;
                        $publicUser['id'] = $user->getId();
                        break;
                }
            }
        }

        $publicUser['groups'] = [];
        //this should be protected by the visiblePublicPreference but it's not yet the case
        foreach ($user->getGroups() as $group) {
            $publicUser['groups'][] = ['name' => $group->getName(), 'id' => $group->getId()];
        }

        return $publicUser;
    }

    /**
     * Serialize the user picture.
     *
     * @param User $user
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
     * @param User $user
     *
     * @return array
     */
    private function serializeMeta(User $user)
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
            'removed' => $user->isRemoved(),
            'locale' => $locale,
            'loggedIn' => $user === $this->tokenStorage->getToken()->getUser(),
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

    /**
     * @param User $user
     *
     * @return array
     */
    private function serializePermissions(User $user)
    {
        $currentUser = $this->tokenStorage->getToken()->getUser();

        $isOwner = $currentUser instanceof User && $currentUser->getUuid() === $user->getUuid();
        $isAdmin = $this->authChecker->isGranted('ROLE_ADMIN'); // todo maybe add those who have access to UserManagement tool

        // todo : move role check elsewhere
        $profileConfig = $this->config->getParameter('profile');
        $editRoles = [];
        if (!empty($profileConfig['roles_edition'])) {
            $editRoles = array_filter($this->tokenStorage->getToken()->getRoles(), function (BaseRole $role) use ($profileConfig) {
                return in_array($role->getRole(), $profileConfig['roles_edition']);
            });
        }

        return [
            'open' => true,
            'contact' => !$isOwner,
            'edit' => $isAdmin || !empty($editRoles) || $isOwner,
            'administrate' => $isAdmin,
            'delete' => $isOwner || $isAdmin, // todo check platform param to now if current user can destroy is account
        ];
    }

    /**
     * @param User $user
     *
     * @return array
     */
    private function serializeRestrictions(User $user)
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

        if (isset($restrictions['dates'])) {
            $dateRange = DateRangeNormalizer::denormalize($restrictions['dates']);

            $user->setInitDate($dateRange[0]);
            $user->setExpirationDate($dateRange[1]);
        }
    }

    /**
     * Deserialize method.
     * TODO This is only a partial implementation.
     *
     * @param array     $data
     * @param User|null $user
     * @param array     $options
     *
     * @return User
     */
    public function deserialize($data, $user = null, array $options = [])
    {
        if (empty($user)) {
            $user = new User();
        }

        // remove this later (with the Trait)
        $user = parent::deserialize($data, $user, $options);

        $this->sipe('picture.url', 'setPicture', $data, $user);
        $this->sipe('username', 'setUserName', $data, $user);
        $this->sipe('firstName', 'setFirstName', $data, $user);
        $this->sipe('lastName', 'setLastName', $data, $user);
        $this->sipe('email', 'setEmail', $data, $user);
        $this->sipe('code', 'setCode', $data, $user);
        //don't trim the password just in case
        $this->sipe('plainPassword', 'setPlainPassword', $data, $user, false);

        if (isset($data['meta'])) {
            $this->deserializeMeta($data['meta'], $user);
        }

        if (isset($data['restrictions'])) {
            $this->deserializeRestrictions($data['restrictions'], $user);
        }

        if (isset($data['mainOrganization'])) {
            $organization = $this->om->getObject($data['mainOrganization'], Organization::class, ['code']);

            if ($organization) {
                $user->addOrganization($organization);
                $user->setMainOrganization($organization);
            }
        }

        //only add role here. If we want to remove them, use the crud remove method instead
        //it's useful if we want to create a user with a list of roles
        if (isset($data['roles'])) {
            foreach ($data['roles'] as $roleData) {
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
                                $wksrq = new WorkspaceRegistrationQueue();
                                $wksrq->setUser($user);
                                $wksrq->setRole($role);
                                $wksrq->setWorkspace($roleWs);
                                $this->om->persist($wksrq);
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
            foreach ($data['groups'] as $group) {
                $group = $this->om->getObject($group, Group::class, ['name']);

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

        $fieldFacets = $this->om
            ->getRepository(FieldFacet::class)
            ->findPlatformFieldFacets();

        /** @var FieldFacet $fieldFacet */
        foreach ($fieldFacets as $fieldFacet) {
            if (isset($data['profile']) && isset($data['profile'][$fieldFacet->getUuid()])) {
                /** @var FieldFacetValue $fieldFacetValue */
                $fieldFacetValue = $this->om
                    ->getRepository(FieldFacetValue::class)
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

        return $user;
    }
}
