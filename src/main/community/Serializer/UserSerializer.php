<?php

namespace Claroline\CommunityBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CommunityBundle\Repository\RoleRepository;
use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Claroline\CoreBundle\Entity\Facet\FieldFacetValue;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\CoreBundle\Library\Normalizer\DateRangeNormalizer;
use Claroline\CoreBundle\Manager\FacetManager;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceUserQueueManager;
use Claroline\CoreBundle\Repository\Facet\FieldFacetRepository;
use Claroline\CoreBundle\Repository\Facet\FieldFacetValueRepository;
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
    private $om;
    /** @var PlatformConfigurationHandler */
    private $config;
    /** @var OrganizationSerializer */
    private $organizationSerializer;
    /** @var FacetManager */
    private $facetManager;
    /** @var WorkspaceUserQueueManager */
    private $workspaceUserQueueManager;

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
        OrganizationSerializer $organizationSerializer,
        FacetManager $facetManager,
        WorkspaceUserQueueManager $workspaceUserQueueManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authorization = $authorization;
        $this->om = $om;
        $this->config = $config;
        $this->organizationSerializer = $organizationSerializer;
        $this->facetManager = $facetManager;
        $this->workspaceUserQueueManager = $workspaceUserQueueManager;

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
        $showEmail = empty($showEmailRoles);
        if ($token && !empty($showEmailRoles)) {
            $isOwner = $token->getUser() instanceof User && $token->getUser()->getId() === $user->getId();
            $showEmail = $isOwner || !empty(array_filter($token->getRoleNames(), function (string $role) use ($showEmailRoles) {
                return 'ROLE_ADMIN' === $role || in_array($role, $showEmailRoles);
            }));
        }

        if (in_array(SerializerInterface::SERIALIZE_MINIMAL, $options)) {
            return [
                'id' => $user->getUuid(),
                'name' => $user->getFullName(), // can be removed later (will throw too much validation errors in JSONSchema for now)
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'username' => $user->getUsername(),
                'email' => $showEmail ? $user->getEmail() : null,
                'picture' => $user->getPicture(),
                'thumbnail' => $user->getThumbnail(),
            ];
        }

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

        $serializedUser = [
            'id' => $user->getUuid(),
            'autoId' => $user->getId(),
            'name' => $user->getFullName(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'username' => $user->getUsername(),
            'picture' => $user->getPicture(),
            'thumbnail' => $user->getThumbnail(),
            'poster' => $user->getPoster(),
            'email' => $showEmail ? $user->getEmail() : null,
            'administrativeCode' => $user->getAdministrativeCode(),
            'phone' => $showEmail ? $user->getPhone() : null,
            'meta' => $this->serializeMeta($user),
            'restrictions' => $this->serializeRestrictions($user),
            // this has a uge negative impact on performances
            // but this is useful to have the user roles in DataLists (mostly in workspace to know which ws role a user has)
            'roles' => array_merge($userRoles, $groupRoles),
        ];

        if (!in_array(SerializerInterface::SERIALIZE_LIST, $options)) {
            if ($user->getMainOrganization()) {
                $serializedUser['mainOrganization'] = $this->organizationSerializer->serialize($user->getMainOrganization(), [SerializerInterface::SERIALIZE_MINIMAL]);
            }
        }

        if (!in_array(SerializerInterface::SERIALIZE_TRANSFER, $options)) {
            $serializedUser['permissions'] = $this->serializePermissions($user);
        }

        if (in_array(Options::SERIALIZE_FACET, $options)) {
            $fields = $this->fieldFacetValueRepo->findPlatformValuesByUser($user);
            if (!empty($fields)) {
                $serializedUser['profile'] = [];
                foreach ($fields as $field) {
                    // we just flatten field facets in the base user structure
                    $serializedUser['profile'][$field->getFieldFacet()->getUuid()] = $this->facetManager->serializeFieldValue(
                        $user,
                        $field->getType(),
                        $field->getValue()
                    );
                }
            }
        }

        return $serializedUser;
    }

    public function deserialize(array $data, User $user, array $options = []): User
    {
        if (!in_array(SerializerInterface::REFRESH_UUID, $options)) {
            $this->sipe('id', 'setUuid', $data, $user);
        } else {
            $user->refreshUuid();
        }

        $this->sipe('username', 'setUserName', $data, $user);
        $this->sipe('firstName', 'setFirstName', $data, $user);
        $this->sipe('lastName', 'setLastName', $data, $user);
        $this->sipe('email', 'setEmail', $data, $user);
        $this->sipe('code', 'setCode', $data, $user);
        $this->sipe('locale', 'setLocale', $data, $user);
        $this->sipe('phone', 'setPhone', $data, $user);
        $this->sipe('administrativeCode', 'setAdministrativeCode', $data, $user);
        $this->sipe('picture', 'setPicture', $data, $user);
        $this->sipe('thumbnail', 'setThumbnail', $data, $user);
        $this->sipe('poster', 'setPoster', $data, $user);

        //don't trim the password just in case
        $this->sipe('plainPassword', 'setPlainPassword', $data, $user, false);

        if (isset($data['meta'])) {
            $this->deserializeMeta($data['meta'], $user);
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

        // TODO : this should not be done here (this is still used to register new users in ws in platform registration)
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

                if ($role && $role->getId()) {
                    $roleWs = $role->getWorkspace();
                    if (in_array(Options::WORKSPACE_VALIDATE_ROLES, $options) && Role::WS_ROLE === $role->getType() && $roleWs->getRegistrationValidation()) {
                        if (!$user->hasRole($role)) {
                            if (!$this->workspaceUserQueueManager->isUserInValidationQueue($roleWs, $user)) {
                                $this->workspaceUserQueueManager->addUserQueue($roleWs, $user, $role);
                            }
                        }
                    } else {
                        $user->addRole($role);
                    }
                }
            }
        }

        if (isset($data['profile'])) {
            $fieldFacets = $this->fieldFacetRepo->findPlatformFieldFacets();
            foreach ($fieldFacets as $fieldFacet) {
                if (array_key_exists($fieldFacet->getUuid(), $data['profile'])) {
                    /** @var FieldFacetValue $fieldFacetValue */
                    $fieldFacetValue = $this->fieldFacetValueRepo // TODO : retrieve all values at once for performances
                        ->findOneBy([
                            'user' => $user,
                            'fieldFacet' => $fieldFacet,
                        ]) ?? new FieldFacetValue();

                    $fieldFacetValue->setUser($user);
                    $fieldFacetValue->setFieldFacet($fieldFacet);

                    $fieldFacetValue->setValue(
                        $this->facetManager->deserializeFieldValue(
                            $user,
                            $fieldFacet->getType(),
                            $data['profile'][$fieldFacet->getUuid()]
                        )
                    );

                    $this->om->persist($fieldFacetValue);
                }
            }
        }

        return $user;
    }

    private function serializeMeta(User $user): array
    {
        return [
            'acceptedTerms' => $user->hasAcceptedTerms(),
            'lastActivity' => DateNormalizer::normalize($user->getLastActivity()),
            'created' => DateNormalizer::normalize($user->getCreated()),
            'description' => $user->getDescription(),
            'mailValidated' => $user->isMailValidated(),
            'mailNotified' => $user->isMailNotified(),
            'personalWorkspace' => (bool) $user->getPersonalWorkspace(),
            'locale' => $user->getLocale(),
        ];
    }

    private function deserializeMeta(array $meta, User $user): void
    {
        $this->sipe('locale', 'setLocale', $meta, $user);
        $this->sipe('description', 'setDescription', $meta, $user);
        $this->sipe('mailNotified', 'setIsMailNotified', $meta, $user);
        $this->sipe('mailValidated', 'setIsMailValidated', $meta, $user);
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

    private function deserializeRestrictions(array $restrictions, User $user): void
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
}
