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

    private RoleRepository $roleRepo;
    private FieldFacetRepository $fieldFacetRepo;
    private FieldFacetValueRepository $fieldFacetValueRepo;

    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly ObjectManager $om,
        private readonly PlatformConfigurationHandler $config,
        private readonly OrganizationSerializer $organizationSerializer,
        private readonly FacetManager $facetManager,
        private readonly WorkspaceUserQueueManager $workspaceUserQueueManager
    ) {
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
                'name' => $user->getFullName(),
                'status' => $user->getStatus(),
                'lastActivity' => DateNormalizer::normalize($user->getLastActivity()),
                'picture' => $user->getPicture(),

                'username' => $user->getUsername(), // required because used to user profile URL
                //'firstName' => $user->getFirstName(),
                //'lastName' => $user->getLastName(),
                //'username' => $user->getUsername(),
                //'email' => $showEmail ? $user->getEmail() : null,
                //'thumbnail' => $user->getThumbnail(),
                //'poster' => $user->getPoster(),
            ];
        }

        $serializedUser = [
            'id' => $user->getUuid(),
            'autoId' => $user->getId(),
            'name' => $user->getFullName(),
            'status' => $user->getStatus(),
            'lastActivity' => DateNormalizer::normalize($user->getLastActivity()),
            'picture' => $user->getPicture(),

            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'username' => $user->getUsername(),
            //'thumbnail' => $user->getThumbnail(),
            'poster' => $user->getPoster(),
            'email' => $showEmail ? $user->getEmail() : null,
            'administrativeCode' => $user->getAdministrativeCode(),
            'phone' => $showEmail ? $user->getPhone() : null,
            'meta' => $this->serializeMeta($user),
            'restrictions' => $this->serializeRestrictions($user),
            'roles' => array_map(function (Role $role) {
                return [
                    'id' => $role->getUuid(),
                    'autoId' => $role->getId(),
                    'name' => $role->getName(),
                    'type' => $role->getType(),
                    'translationKey' => $role->getTranslationKey(),
                ];
            }, $user->getEntityRoles()),
        ];

        if (!in_array(SerializerInterface::SERIALIZE_LIST, $options)) {
            if ($user->getMainOrganization()) {
                $serializedUser['mainOrganization'] = $this->organizationSerializer->serialize($user->getMainOrganization(), [SerializerInterface::SERIALIZE_MINIMAL]);
            }
        }

        if (!in_array(SerializerInterface::SERIALIZE_TRANSFER, $options)) {
            $serializedUser['status'] = $user->getStatus();
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
            'lastActivity' => DateNormalizer::normalize($user->getLastActivity()), // deprecated
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
        $this->sipe('mailNotified', 'setMailNotified', $meta, $user);
        $this->sipe('mailValidated', 'setMailValidated', $meta, $user);
    }

    private function serializePermissions(User $user): array
    {
        return [
            'open' => $this->authorization->isGranted('OPEN', $user),
            'edit' => $this->authorization->isGranted('EDIT', $user),
            'administrate' => $this->authorization->isGranted('ADMINISTRATE', $user),
            'delete' => $this->authorization->isGranted('DELETE', $user),
        ];
    }

    private function serializeRestrictions(User $user): array
    {
        return [
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
