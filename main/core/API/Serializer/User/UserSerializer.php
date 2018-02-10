<?php

namespace Claroline\CoreBundle\API\Serializer\User;

use Claroline\CoreBundle\API\Options;
use Claroline\CoreBundle\API\Serializer\File\PublicFileSerializer;
use Claroline\CoreBundle\API\Serializer\SerializerTrait;
use Claroline\CoreBundle\Entity\Facet\FieldFacetValue;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Normalizer\DateRangeNormalizer;
use Claroline\CoreBundle\Manager\FacetManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

/**
 * @DI\Service("claroline.serializer.user")
 * @DI\Tag("claroline.serializer")
 */
class UserSerializer
{
    use SerializerTrait;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var AuthorizationCheckerInterface */
    private $authChecker;

    /** @var EncoderFactoryInterface */
    private $encoderFactory;

    /** @var ObjectManager */
    private $om;

    /** @var PlatformConfigurationHandler */
    private $config;

    /** @var FacetManager */
    private $facetManager;

    /** @var PublicFileSerializer */
    private $fileSerializer;

    /** @var ContainerInterface */
    private $container;

    /** @var ContainerInterface */
    private $container;

    /**
     * UserManager constructor.
     *
     * @DI\InjectParams({
     *     "tokenStorage"   = @DI\Inject("security.token_storage"),
     *     "authChecker"    = @DI\Inject("security.authorization_checker"),
     *     "encoderFactory" = @DI\Inject("security.encoder_factory"),
     *     "om"             = @DI\Inject("claroline.persistence.object_manager"),
     *     "config"         = @DI\Inject("claroline.config.platform_config_handler"),
     *     "facetManager"   = @DI\Inject("claroline.manager.facet_manager"),
     *     "fileSerializer" = @DI\Inject("claroline.serializer.public_file"),
     *     "container"      = @DI\Inject("service_container")
     * })
     *
     * @param TokenStorageInterface         $tokenStorage
     * @param AuthorizationCheckerInterface $authChecker
     * @param EncoderFactoryInterface       $encoderFactory
     * @param ObjectManager                 $om
     * @param PlatformConfigurationHandler  $config
     * @param FacetManager                  $facetManager
     * @param PublicFileSerializer          $fileSerializer
     * @param ContainerInterface            $container
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authChecker,
        EncoderFactoryInterface $encoderFactory,
        ObjectManager $om,
        PlatformConfigurationHandler $config,
        FacetManager $facetManager,
        PublicFileSerializer $fileSerializer,
        ContainerInterface $container
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authChecker = $authChecker;
        $this->encoderFactory = $encoderFactory;
        $this->om = $om;
        $this->config = $config;
        $this->facetManager = $facetManager;
        $this->fileSerializer = $fileSerializer;
        $this->container = $container;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return 'Claroline\CoreBundle\Entity\User';
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
    public function serialize(User $user, array $options = [])
    {
        if (isset($options['public']) && $options['public']) {
            return $this->serializePublic($user);
        }

        $serialized = [
            'autoId' => $user->getId(), //for old compatibility purposes
            'id' => $user->getUuid(),
            'name' => $user->getFirstName().' '.$user->getLastName(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'username' => $user->getUsername(),
            'picture' => $this->serializePicture($user),
            'email' => $user->getEmail(),
            'administrativeCode' => $user->getAdministrativeCode(),
            'phone' => $user->getPhone(),
        ];

        if (!in_array(Options::SERIALIZE_MINIMAL, $options)) {
            $serialized = array_merge($serialized, [
                'meta' => $this->serializeMeta($user),
                'restrictions' => $this->serializeRestrictions($user),
                'rights' => $this->serializeRights($user),
                'roles' => array_map(function (Role $role) {
                    return [
                        'id' => $role->getUuid(),
                        'type' => $role->getType(),
                        'name' => $role->getName(),
                        'translationKey' => $role->getTranslationKey(),
                    ];
                }, $user->getEntityRoles()),
                'groups' => array_map(function (Group $group) {
                    return [
                        'id' => $group->getUuid(),
                        'name' => $group->getName(),
                    ];
                }, $user->getGroups()->toArray()),
            ]);
        }

        if (in_array(Options::SERIALIZE_FACET, $options)) {
            $fields = $this->om
                ->getRepository('Claroline\CoreBundle\Entity\Facet\FieldFacetValue')
                ->findOneBy(['user' => $user]);

            /** @var FieldFacetValue $field */
            foreach ($fields as $field) {
                // we just flatten field facets in the base user structure
                $serialized[$field->getFieldFacet()->getName()] = $field->getValue();
            }
        }

        return $serialized;
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
                        $publicUser['mail'] = $user->getMail();
                        break;
                    case 'phone':
                        $publicUser['phone'] = $user->getPhone();
                        break;
                    case 'sendMail':
                        $publicUser['mail'] = $user->getMail();
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
        /** @var PublicFile $file */
        $file = $this->om
          ->getRepository('Claroline\CoreBundle\Entity\File\PublicFile')
          ->findOneBy(['url' => $user->getPicture()]);

        if ($file) {
            return $this->fileSerializer->serialize($file);
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
            'acceptedTerms' => $user->hasAcceptedTerms(),
            'lastLogin' => $user->getLastLogin() ? $user->getLastLogin()->format('Y-m-d\TH:i:s') : null,
            'created' => $user->getCreated() ? $user->getCreated()->format('Y-m-d\TH:i:s') : null,
            'description' => $user->getDescription(),
            'mailValidated' => $user->isMailValidated(),
            'mailNotified' => $user->isMailNotified(),
            'mailWarningHidden' => $user->getHideMailWarning(),
            'publicUrlTuned' => $user->hasTunedPublicUrl(),
            'authentication' => $user->getAuthentication(),
            'personalWorkspace' => (bool) $user->getPersonalWorkspace(),
            'enabled' => $user->isEnabled(),
            'removed' => $user->isRemoved(),
            'locale' => $locale,
        ];
    }

    private function deserializeMeta(array $meta, User $user)
    {
        $this->sipe('enabled', 'setIsEnabled', $meta, $user);
        if (empty($meta) || empty($meta['locale'])) {
            if (empty($user->getLocale())) {
                // set default
                $user->setLocale($this->config->getParameter('locale_language'));
            }
        } else {
            // use given locale
            $user->setLocale($meta['locale']);
        }

        $this->sipe('description', 'setDescription', $meta, $user);
    }

    /**
     * @param User $user
     *
     * @return array
     */
    private function serializeRights(User $user)
    {
        $currentUser = $this->tokenStorage->getToken()->getUser();

        $isOwner = $currentUser instanceof User && $currentUser->getUuid() === $user->getUuid();
        $isAdmin = $this->authChecker->isGranted('ROLE_ADMIN'); // todo maybe add those who have access to UserManagement tool

        // return same structure than ResourceNode
        return [
            'current' => [
                'contact' => !$isOwner,
                'edit' => $isOwner || $isAdmin,
                'administrate' => $isAdmin,
                'delete' => $isOwner || $isAdmin, // todo check platform param to now if current user can destroy is account
            ],
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
            'dates' => DateRangeNormalizer::normalize($user->getInitDate(), $user->getExpirationDate()),
        ];
    }

    private function deserializeRestrictions(array $restrictions, User $user)
    {
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
    public function deserialize(array $data, User $user = null, array $options = [])
    {
        // remove this later (with the Trait)
        $object = $this->genericSerializer->deserialize($data, $user, $options);

        $this->sipe('email', 'setEmail', $data, $object);

        $this->deserializeMeta($data['meta'], $user);
        $this->deserializeRestrictions($data['restrictions'], $user);

        $this->sipe('picture.url', 'setPicture', $data, $object);

        if (!empty($data['plainPassword'])) {
            $object->setPlainPassword($data['plainPassword']);

            $password = $this->encoderFactory
                ->getEncoder($object)
                ->encodePassword($object->getPlainPassword(), $user->getSalt());

            $object->setPassword($password);
        }

        //avoid recursive dependencies
        $finder = $this->container->get('claroline.api.finder');
        $serializer = $this->container->get('claroline.api.serializer');
        $fieldFacets = $finder->search('Claroline\CoreBundle\Entity\Facet\FieldFacet');

        foreach ($fieldFacets['data'] as $fieldFacet) {
            foreach (array_keys($data) as $propertyName) {
                if ($this->getPrettyName($propertyName) === $this->getPrettyName($fieldFacet['name'])) {
                    $fieldFacetValue = [
                        'name' => $propertyName,
                        'value' => $data[$propertyName],
                        'user' => ['id' => $data['id']],
                        'fieldFacet' => ['id' => $fieldFacet['id']],
                    ];

                    //if the fieldFacetValue exists, we'll find it's id so we don't have to create a new one
                    $fieldFacetValues = $finder->search('Claroline\CoreBundle\Entity\Facet\FieldFacetValue', [
                        'filters' => ['user' => $data['id'], 'fieldFacet' => $fieldFacet['id']],
                    ]);

                    if (count($fieldFacetValues['data']) > 0) {
                        $fieldFacetValue['id'] = $fieldFacetValues['data'][0]['id'];
                    }

                    $user->addFieldFacet(
                        $serializer->deserialize('Claroline\CoreBundle\Entity\Facet\FieldFacetValue', $fieldFacetValue)
                    );
                }
            }
        }

        return $object;
    }

    /**
     * @return string
     *
     * Maybe move this somewhere else or in a trait
     */
    private function getPrettyName($name)
    {
        $string = str_replace(' ', '-', $name); // Replaces all spaces with hyphens.
        $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
        $string = preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.

        return strtolower($string);
    }
}
