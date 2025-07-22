<?php

namespace Claroline\CommunityBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Manager\UserPreferencesManager;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CommunityBundle\Entity\UserProfile;
use Claroline\CommunityBundle\Repository\UserProfileRepository;
use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Claroline\CoreBundle\Entity\Facet\FieldFacetValue;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\CoreBundle\Library\Normalizer\DateRangeNormalizer;
use Claroline\CoreBundle\Manager\FacetManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class UserSerializer
{
    use SerializerTrait;

    private UserProfileRepository $userProfileRepo;

    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly ObjectManager $om,
        private readonly PlatformConfigurationHandler $config,
        private readonly FacetManager $facetManager,
        private readonly UserPreferencesManager $userPreferencesManager,
    ) {
        $this->userProfileRepo = $om->getRepository(UserProfile::class);
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
            $showEmail = $isOwner || !empty(array_filter($token?->getRoleNames() ?? [], function (string $role) use ($showEmailRoles) {
                return 'ROLE_ADMIN' === $role || in_array($role, $showEmailRoles);
            }));
        }

        if (in_array(SerializerInterface::SERIALIZE_MINIMAL, $options)) {
            return [
                'id' => $user->getUuid(),
                'name' => $user->getFullName(),
                'status' => $user->getStatus(),
                'picture' => $user->getPicture(),
                'username' => $user->getUsername(), // required because used by the user profile URL
                'poster' => $user->getPoster(),
                'email' => $showEmail ? $user->getEmail() : null, // for export
                'firstName' => $user->getFirstName(), // for export
                'lastName' => $user->getLastName(), // for export
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
            'poster' => $user->getPoster(),
            'email' => $showEmail ? $user->getEmail() : null,
            'administrativeCode' => $user->getAdministrativeCode(),
            'phone' => $showEmail ? $user->getPhone() : null,
            'meta' => $this->serializeMeta($user),
            'restrictions' => [
                'disabled' => $user->isDisabled(),
                'removed' => $user->isRemoved(),
                'dates' => DateRangeNormalizer::normalize($user->getInitDate(), $user->getExpirationDate()),
            ],
            'roles' => array_merge(
                array_map(function (Role $role) {
                    return [
                        'id' => $role->getUuid(),
                        'autoId' => $role->getId(),
                        'name' => $role->getName(),
                        'type' => $role->getType(),
                        'translationKey' => $role->getTranslationKey(),
                        'context' => 'user',
                    ];
                }, $user->getEntityRoles(false)),
                array_map(function (Role $role) {
                    return [
                        'id' => $role->getUuid(),
                        'autoId' => $role->getId(),
                        'name' => $role->getName(),
                        'type' => $role->getType(),
                        'translationKey' => $role->getTranslationKey(),
                        'context' => 'group',
                    ];
                }, $user->getGroupRoles()),
            ),
        ];

        if (!in_array(SerializerInterface::SERIALIZE_TRANSFER, $options)) {
            $serializedUser['status'] = $user->getStatus();
            $serializedUser['permissions'] = $this->serializePermissions($user);

            // this should be moved outside the serializer
            $serializedUser['preferences'] = $this->userPreferencesManager->getPreferences($user);
        }

        if (0 !== $user->getProfileValues()->count()) {
            $serializedUser['profile'] = [];
            foreach ($user->getProfileValues() as $field) {
                // we just flatten field facets in the base user structure
                $serializedUser['profile'][$field->getFieldFacet()->getUuid()] = $this->facetManager->serializeFieldValue(
                    $user,
                    $field->getType(),
                    $field->getValue()
                );
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
        $this->sipe('poster', 'setPoster', $data, $user);

        // don't trim the password just in case
        $this->sipe('plainPassword', 'setPlainPassword', $data, $user, false);

        if (isset($data['meta'])) {
            $this->deserializeMeta($data['meta'], $user);
        }

        if (isset($data['restrictions'])) {
            $this->deserializeRestrictions($data['restrictions'], $user);
        }

        if (array_key_exists('profile', $data)) {
            foreach ($data['profile'] as $fieldId => $fieldValue) {
                $fieldFacetValue = $user->getProfileValue($fieldId) ?? new FieldFacetValue();
                $fieldFacet = $this->om->getRepository(FieldFacet::class)->findOneBy(['uuid' => $fieldId]);
                if (empty($fieldFacet)) {
                    $user->removeProfileValue($fieldFacetValue);
                } else {
                    $fieldFacetValue->setFieldFacet($fieldFacet);
                    $fieldFacetValue->setValue(
                        $this->facetManager->deserializeFieldValue(
                            $user,
                            $fieldFacet->getType(),
                            $fieldValue
                        )
                    );

                    $user->addProfileValue($fieldFacetValue);
                }
            }
        }

        return $user;
    }

    private function serializeMeta(User $user): array
    {
        return [
            'acceptedTerms' => $user->hasAcceptedTerms(),
            'created' => DateNormalizer::normalize($user->getCreated()),
            'description' => $user->getDescription(),
            'mailValidated' => $user->isMailValidated(),
            'mailNotified' => $user->isMailNotified(),
            // 'personalWorkspace' => (bool) $user->getPersonalWorkspace(),
            'locale' => $user->getLocale(),
        ];
    }

    private function deserializeMeta(array $meta, User $user): void
    {
        $this->sipe('locale', 'setLocale', $meta, $user);
        $this->sipe('description', 'setDescription', $meta, $user);
        $this->sipe('mailNotified', 'setMailNotified', $meta, $user);
        $this->sipe('mailValidated', 'setMailValidated', $meta, $user);
        $this->sipe('acceptedTerms', 'setAcceptedTerms', $meta, $user);
    }

    private function serializePermissions(User $user): array
    {
        $administrate = $this->authorization->isGranted('ADMINISTRATE', $user);

        return [
            'open' => $administrate || $this->authorization->isGranted('OPEN', $user),
            'edit' => $administrate || $this->authorization->isGranted('EDIT', $user),
            'administrate' => $administrate,
            'delete' => $administrate || $this->authorization->isGranted('DELETE', $user),
        ];
    }

    private function deserializeRestrictions(array $restrictions, User $user): void
    {
        if (isset($restrictions['disabled'])) {
            $user->setDisabled($restrictions['disabled']);
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
