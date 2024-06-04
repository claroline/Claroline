<?php

namespace Claroline\CommunityBundle\Validator;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Utils\ArrayUtils;
use Claroline\AppBundle\API\ValidatorInterface;
use Claroline\AppBundle\API\ValidatorProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\AuthenticationBundle\Entity\AuthenticationParameters;
use Claroline\CommunityBundle\Repository\RoleRepository;
use Claroline\CommunityBundle\Serializer\ProfileSerializer;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\FacetManager;
use Claroline\CoreBundle\Manager\Organization\OrganizationManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use Claroline\CoreBundle\Security\PlatformRoles;
use Claroline\CoreBundle\Security\ToolPermissions;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class UserValidator implements ValidatorInterface
{
    private RoleRepository $roleRepo;

    public function __construct(
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly ObjectManager $om,
        private readonly PlatformConfigurationHandler $config,
        private readonly UserManager $manager,
        private readonly WorkspaceManager $workspaceManager,
        private readonly OrganizationManager $organizationManager,
        private readonly FacetManager $facetManager,
        private readonly ProfileSerializer $profileSerializer
    ) {
        $this->roleRepo = $om->getRepository(Role::class);
    }

    public static function getClass(): string
    {
        return User::class;
    }

    public function getUniqueFields(): array
    {
        $unique = [
            'username' => 'username',
            'email' => 'email',
        ];

        if ($this->config->getParameter('is_user_admin_code_unique')) {
            $unique['administrativeCode'] = 'administrativeCode';
        }

        return $unique;
    }

    public function validate(array $data, string $mode, array $options = []): array
    {
        $errors = [];

        // implements something cleaner later
        if (ValidatorProvider::UPDATE === $mode && !isset($data['id'])) {
            return $errors;
        }

        if (ValidatorProvider::CREATE === $mode) {
            // check the platform user limit
            if ($this->manager->hasReachedLimit()) {
                $errors[] = [
                    'path' => '',
                    'message' => 'The user limit of the platform has been reached.',
                ];
            }
        }

        $errors = array_merge($errors, $this->validateRoles($data, $mode));

        // validate username
        if ($this->config->getParameter('community.username')) {
            // validate username format
            $regex = $this->config->getParameter('username_regex');
            if (isset($data['username']) && $regex && !preg_match($regex, $data['username'])) {
                $errors[] = [
                    'path' => 'username',
                    'message' => 'The username '.$data['username'].' contains illegal characters.',
                ];
            }
        }

        // Password check
        if (isset($data['plainPassword'])) {
            $errors = array_merge($errors, $this->validatePasswordCheck($data['plainPassword']));
        }

        // todo validate Facet values
        if (in_array(Options::VALIDATE_FACET, $options)) {
            $facets = $this->profileSerializer->serialize(in_array(Options::REGISTRATION, $options) ? [Options::REGISTRATION] : []);
            $allFields = [];
            $required = [];

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

    private function validateRoles(array $data, string $mode): array
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
            $nonAuthorized = array_filter($roles, function (Role $role) use ($data, $mode) {
                if ($this->authorization->isGranted(PlatformRoles::ADMIN)) {
                    return false;
                }

                if ($this->isOrganizationManager($this->tokenStorage->getToken(), $data, $mode)) {
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
                    if ($this->authorization->isGranted(ToolPermissions::getPermission('community', 'REGISTER'), $workspace)) {
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

    private function isOrganizationManager(TokenInterface $token, array $userData, string $mode): bool
    {
        if (!($token->getUser() instanceof User)) {
            return false;
        }

        $userOrganizations = [];
        if (ValidatorProvider::UPDATE === $mode) {
            // retrieve current organization of the user
            $userOrganizations = $this->om->getRepository(Organization::class)->findByMember($userData['id']);
        }

        if (isset($userData['mainOrganization'])) {
            $mainOrganization = $this->om->getObject($userData['mainOrganization'], Organization::class, ['id', 'code', 'name', 'email']);
            if (!empty($mainOrganization)) {
                $userOrganizations[] = $mainOrganization;
            }
        }

        if (empty($userOrganizations)) {
            // user will go in default organization
            $userOrganizations[] = $this->organizationManager->getDefault();
        }

        $adminOrganizations = $token->getUser()->getAdministratedOrganizations();
        foreach ($adminOrganizations as $adminOrganization) {
            foreach ($userOrganizations as $userOrganization) {
                if ($userOrganization === $adminOrganization) {
                    return true;
                }
            }
        }

        return false;
    }

    private function validatePasswordCheck(string $password): array
    {
        $errors = [];
        $authenticationParameters = $this->om->getRepository(AuthenticationParameters::class)->findOneBy([]);
        if ($authenticationParameters) {
            if ($authenticationParameters->getMinLength() > 0 && strlen($password) < $authenticationParameters->getMinLength()) {
                $errors[] = [
                    'path' => 'plainPassword',
                    'message' => 'The password must be at least '.$authenticationParameters->getMinLength().' characters long.',
                ];
            }

            if ($authenticationParameters->getRequireLowercase() && !preg_match('/[a-z]/', $password)) {
                $errors[] = [
                    'path' => 'plainPassword',
                    'message' => 'The password must contain at least one lowercase letter.',
                ];
            }

            if ($authenticationParameters->getRequireUppercase() && !preg_match('/[A-Z]/', $password)) {
                $errors[] = [
                    'path' => 'plainPassword',
                    'message' => 'The password must contain at least one uppercase letter.',
                ];
            }

            if ($authenticationParameters->getRequireNumber() && !preg_match('/[0-9]/', $password)) {
                $errors[] = [
                    'path' => 'plainPassword',
                    'message' => 'The password must contain at least one number.',
                ];
            }

            if ($authenticationParameters->getRequireSpecialChar() && !preg_match('/[^a-zA-Z0-9]/', $password)) {
                $errors[] = [
                    'path' => 'plainPassword',
                    'message' => 'The password must contain at least one special character.',
                ];
            }
        }

        return $errors;
    }
}
