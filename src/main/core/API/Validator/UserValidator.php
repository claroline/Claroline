<?php

namespace Claroline\CoreBundle\API\Validator;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Utils\ArrayUtils;
use Claroline\AppBundle\API\ValidatorInterface;
use Claroline\AppBundle\API\ValidatorProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\User\ProfileSerializer;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\FacetManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use Claroline\CoreBundle\Security\PlatformRoles;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class UserValidator implements ValidatorInterface
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var ObjectManager */
    private $om;
    /** @var PlatformConfigurationHandler */
    private $config;
    /** @var UserManager */
    private $manager;
    /** @var WorkspaceManager */
    private $workspaceManager;
    /** @var ProfileSerializer */
    private $profileSerializer;
    /** @var FacetManager */
    private $facetManager;

    private $roleRepo;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        TokenStorageInterface $tokenStorage,
        ObjectManager $om,
        PlatformConfigurationHandler $config,
        UserManager $manager,
        WorkspaceManager $workspaceManager,
        ProfileSerializer $profileSerializer
    ) {
        $this->authorization = $authorization;
        $this->tokenStorage = $tokenStorage;
        $this->om = $om;
        $this->config = $config;
        $this->manager = $manager;
        $this->workspaceManager = $workspaceManager;
        $this->profileSerializer = $profileSerializer;

        $this->roleRepo = $om->getRepository(Role::class);
    }

    public static function getClass(): string
    {
        return User::class;
    }

    public function getUniqueFields()
    {
        return [
            'username' => 'username',
            'email' => 'email',
        ];
    }

    public function validate($data, $mode, array $options = [])
    {
        $errors = [];

        // implements something cleaner later
        if (ValidatorProvider::UPDATE === $mode && !isset($data['id'])) {
            return $errors;
        }

        if (ValidatorProvider::CREATE === $mode) {
            // check the platform user limit
            $restrictions = $this->config->getParameter('restrictions') ?? [];
            if (isset($restrictions['users']) && isset($restrictions['max_users']) && $restrictions['users'] && $restrictions['max_users']) {
                $usersCount = $this->manager->countEnabledUsers();
                if ($usersCount >= $restrictions['max_users']) {
                    $errors[] = [
                        'path' => '',
                        'message' => 'The user limit of the platform has been reached.',
                    ];
                }
            }
        }

        $errors = array_merge($errors, $this->validateRoles($data));

        // validate username format
        $regex = $this->config->getParameter('username_regex');
        if ($regex && !preg_match($regex, $data['username'])) {
            $errors[] = [
                'path' => 'username',
                'message' => 'The username '.$data['username'].' contains illegal characters.',
            ];
        }

        // checks username is available
        if (isset($data['username']) && $this->exists('username', $data['username'], isset($data['id']) ? $data['id'] : null)) {
            $errors[] = [
                'path' => 'username',
                'message' => 'The username '.$data['username'].' already exists.',
            ];
        }

        // check email is not already used
        if (isset($data['email']) && $this->exists('email', $data['email'], isset($data['id']) ? $data['id'] : null)) {
            $errors[] = [
                'path' => 'email',
                'message' => 'The email '.$data['email'].' already exists.',
            ];
        }

        // check if the administrative code is unique if the platform is configured to
        if (isset($data['administrativeCode']) && $this->config->getParameter('is_user_admin_code_unique')) {
            if ($this->exists('administrativeCode', $data['administrativeCode'], isset($data['id']) ? $data['id'] : null)) {
                $errors[] = [
                    'path' => '/administrativeCode',
                    'message' => 'The administrative code '.$data['administrativeCode'].' already exists.',
                ];
            }
        }

        // todo validate Facet values
        if (in_array(Options::VALIDATE_FACET, $options)) {
            $facets = $this->profileSerializer->serialize([Options::REGISTRATION]);
            $required = [];
            $allFields = [];

            foreach ($facets as $facet) {
                foreach ($facet['sections'] as $section) {
                    foreach ($section['fields'] as $field) {
                        $allFields[] = $field;
                        if ($field['required']) {
                            $required[] = $field;
                        }
                    }
                }
            }

            foreach ($required as $field) {
                if ($this->facetManager->isFieldDisplayed($field, $allFields, $data) && !ArrayUtils::has($data, 'profile.'.$field['id'])) {
                    $errors[] = [
                        'path' => 'profile/'.$field['id'],
                        'message' => 'The field '.$field['label'].' is required',
                    ];
                }
            }
        }

        return $errors;
    }

    private function validateRoles(array $data)
    {
        if (!empty($data['roles'])) {
            // get the entities for the roles we try to add to the user
            $roles = array_filter(array_map(function (array $roleData) {
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

                return $role;
            }, $data['roles']), function ($role) {
                return !empty($role);
            });

            // check if the current user can add those roles to the edited/created user
            // this is a c/c from AbstractRoleSubjectVoter. We should find a way to merge it
            $nonAuthorized = array_filter($roles, function (Role $role) use ($data) {
                if (!$this->authorization->isGranted(PlatformRoles::ADMIN)) {
                    return false;
                }

                if (Role::USER_ROLE === $role->getType()) {
                    $user = !empty($role->getUsers()) ? $role->getUsers()[0] : null;
                    if ($user->getUsername() === $data['username']) {
                        return false;
                    }
                }

                $workspace = $role->getWorkspace();
                if ($workspace) {
                    if ($this->authorization->isGranted(['community', 'create_user'], $workspace)) {
                        // If user is workspace manager then grant access
                        if ($this->workspaceManager->isManager($workspace, $this->tokenStorage->getToken())) {
                            return false;
                        }

                        // Otherwise only allow modification of roles the current user owns
                        if (in_array($role->getName(), $this->tokenStorage->getToken()->getRoleNames())) {
                            return false;
                        }
                    }

                    // If public registration is enabled and user try to get the default role, grant access
                    if ($workspace->getSelfRegistration() && $workspace->getDefaultRole()) {
                        if ($workspace->getDefaultRole()->getId() === $role->getId()) {
                            return false;
                        }
                    }

                    // user has no community right on the workspace he cannot add anything
                    return true;
                }

                if (Role::PLATFORM_ROLE === $role->getType()) {
                    // we can only add platform roles to users if we have that platform role
                    if (in_array($role->getName(), $this->tokenStorage->getToken()->getRoleNames())) {
                        return false;
                    }

                    if ($this->config->getParameter('registration.self')) {
                        $defaultRole = $this->config->getParameter('registration.default_role');
                        if ($role->getName() === $defaultRole) {
                            return false;
                        }
                    }
                }

                return true;
            });

            return array_map(function (Role $role) {
                return [
                    'path' => '/roles',
                    'message' => sprintf('Unauthorized role %s', $role->getName()),
                ];
            }, $nonAuthorized);
        }

        return [];
    }

    /**
     * Check if a user exists with the given data.
     *
     * @param string      $propName
     * @param string      $propValue
     * @param string|null $userId
     *
     * @return bool
     */
    private function exists($propName, $propValue, $userId = null)
    {
        $qb = $this->om->createQueryBuilder();
        $qb
            ->select('COUNT(DISTINCT user)')
            ->from('Claroline\CoreBundle\Entity\User', 'user')
            ->where('user.'.$propName.' = :value')
            ->setParameter('value', $propValue);

        if (isset($userId)) {
            $parameter = is_numeric($userId) ? 'id' : 'uuid';
            $qb->andWhere("user.{$parameter} != :{$parameter}")->setParameter($parameter, $userId);
        }

        return 0 < $qb->getQuery()->getSingleScalarResult();
    }
}
