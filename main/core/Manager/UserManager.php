<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\BundleRecorder\Log\LoggableTrait;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\UserOptions;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Configuration\PlatformDefaults;
use Claroline\CoreBundle\Library\Security\PlatformRoles;
use Claroline\CoreBundle\Library\Utilities\FileUtilities;
use Claroline\CoreBundle\Manager\Exception\AddRoleException;
use Claroline\CoreBundle\Manager\Organization\OrganizationManager;
use Claroline\CoreBundle\Pager\PagerFactory;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\DiExtraBundle\Annotation as DI;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * @DI\Service("claroline.manager.user_manager")
 */
class UserManager
{
    use LoggableTrait;

    const MAX_USER_BATCH_SIZE = 100;
    const MAX_EDIT_BATCH_SIZE = 100;

    private $container;
    private $groupManager;
    private $mailManager;
    private $objectManager;
    private $organizationManager;
    private $pagerFactory;
    private $personalWsTemplateFile;
    private $platformConfigHandler;
    private $roleManager;
    private $strictEventDispatcher;
    private $tokenStorage;
    private $toolManager;
    private $transferManager;
    private $translator;
    private $uploadsDirectory;
    private $validator;
    private $workspaceManager;
    private $userRepo;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "container"              = @DI\Inject("service_container"),
     *     "groupManager"           = @DI\Inject("claroline.manager.group_manager"),
     *     "mailManager"            = @DI\Inject("claroline.manager.mail_manager"),
     *     "objectManager"          = @DI\Inject("claroline.persistence.object_manager"),
     *     "organizationManager"    = @DI\Inject("claroline.manager.organization.organization_manager"),
     *     "pagerFactory"           = @DI\Inject("claroline.pager.pager_factory"),
     *     "personalTemplate"       = @DI\Inject("%claroline.param.personal_template%"),
     *     "platformConfigHandler"  = @DI\Inject("claroline.config.platform_config_handler"),
     *     "roleManager"            = @DI\Inject("claroline.manager.role_manager"),
     *     "strictEventDispatcher"  = @DI\Inject("claroline.event.event_dispatcher"),
     *     "tokenStorage"           = @DI\Inject("security.token_storage"),
     *     "toolManager"            = @DI\Inject("claroline.manager.tool_manager"),
     *     "transferManager"        = @DI\Inject("claroline.manager.transfer_manager"),
     *     "translator"             = @DI\Inject("translator"),
     *     "uploadsDirectory"       = @DI\Inject("%claroline.param.uploads_directory%"),
     *     "validator"              = @DI\Inject("validator"),
     *     "workspaceManager"       = @DI\Inject("claroline.manager.workspace_manager"),
     *     "fu"                     = @DI\Inject("claroline.utilities.file")
     * })
     */
    public function __construct(
        ContainerInterface $container,
        GroupManager $groupManager,
        MailManager $mailManager,
        ObjectManager $objectManager,
        OrganizationManager $organizationManager,
        PagerFactory $pagerFactory,
        $personalTemplate,
        PlatformConfigurationHandler $platformConfigHandler,
        RoleManager $roleManager,
        StrictDispatcher $strictEventDispatcher,
        TokenStorageInterface $tokenStorage,
        ToolManager $toolManager,
        TransferManager $transferManager,
        TranslatorInterface $translator,
        $uploadsDirectory,
        ValidatorInterface $validator,
        WorkspaceManager $workspaceManager,
        FileUtilities $fu
    ) {
        $this->container = $container;
        $this->groupManager = $groupManager;
        $this->mailManager = $mailManager;
        $this->objectManager = $objectManager;
        $this->organizationManager = $organizationManager;
        $this->pagerFactory = $pagerFactory;
        $this->personalWsTemplateFile = $personalTemplate;
        $this->platformConfigHandler = $platformConfigHandler;
        $this->roleManager = $roleManager;
        $this->strictEventDispatcher = $strictEventDispatcher;
        $this->tokenStorage = $tokenStorage;
        $this->toolManager = $toolManager;
        $this->transferManager = $transferManager;
        $this->translator = $translator;
        $this->uploadsDirectory = $uploadsDirectory;
        $this->validator = $validator;
        $this->workspaceManager = $workspaceManager;
        $this->userRepo = $objectManager->getRepository('ClarolineCoreBundle:User');
        $this->fu = $fu;
    }

    /**
     * Create a user.
     * Its basic properties (name, username,... ) must already be set.
     *
     * @param \Claroline\CoreBundle\Entity\User $user
     * @param bool                              $sendMail         do we need to mail the new user ?
     * @param array                             $additionnalRoles a list of additionalRoles
     * @param Model                             $model            a model to create workspace
     *
     * @return \Claroline\CoreBundle\Entity\User
     */
    public function createUser(
        User $user,
        $sendMail = true,
        $rolesToAdd = [],
        $model = null,
        $publicUrl = null,
        $organizations = [],
        $forcePersonalWorkspace = null,
        $forceRoleValidation = true
    ) {
        $additionnalRoles = [];

        foreach ($rolesToAdd as $roleToAdd) {
            $additionnalRoles[] = is_string($roleToAdd) ? $this->roleManager->getRoleByName($roleToAdd) : $roleToAdd;
        }

        if (count($organizations) === 0 && count($user->getOrganizations()) === 0) {
            $organizations = [$this->organizationManager->getDefault(true)];
            $user->setOrganizations($organizations);
        }

        $this->objectManager->startFlushSuite();
        $user->setGuid($this->container->get('claroline.utilities.misc')->generateGuid());
        $user->setEmailValidationHash($this->container->get('claroline.utilities.misc')->generateGuid());
        $user->setOrganizations($organizations);
        $publicUrl ? $user->setPublicUrl($publicUrl) : $user->setPublicUrl($this->generatePublicUrl($user));
        $this->toolManager->addRequiredToolsToUser($user, 0);
        $this->toolManager->addRequiredToolsToUser($user, 1);
        $roleUser = $this->roleManager->getRoleByName(PlatformRoles::USER);
        $user->addRole($roleUser);
        $this->roleManager->createUserRole($user);

        foreach ($additionnalRoles as $role) {
            if ($role) {
                $this->roleManager->associateRole($user, $role);
            }
        }

        if ($this->mailManager->isMailerAvailable() && $sendMail) {
            //send a validation by hash
            $mailValidation = $this->platformConfigHandler->getParameter('registration_mail_validation');
            if ($mailValidation === PlatformDefaults::REGISTRATION_MAIL_VALIDATION_FULL) {
                $password = sha1(rand(1000, 10000).$user->getUsername().$user->getSalt());
                $user->setResetPasswordHash($password);
                $user->setIsEnabled(false);
                $this->mailManager->sendEnableAccountMessage($user);
            } elseif ($mailValidation === PlatformDefaults::REGISTRATION_MAIL_VALIDATION_PARTIAL) {
                //don't change anything
                $this->mailManager->sendCreationMessage($user);
            }
        }

        if ($forcePersonalWorkspace !== null) {
            if ($forcePersonalWorkspace) {
                $this->setPersonalWorkspace($user, $model);
            }
        } else {
            if ($this->personalWorkspaceAllowed($additionnalRoles)) {
                $this->setPersonalWorkspace($user, $model);
            }
        }

        $this->objectManager->persist($user);
        $this->strictEventDispatcher->dispatch('user_created_event', 'UserCreated', ['user' => $user]);
        $this->strictEventDispatcher->dispatch('log', 'Log\LogUserCreate', [$user]);
        $this->objectManager->endFlushSuite();

        return $user;
    }

    /**
     * Persist a user.
     *
     * @param User $user
     *
     * @return User
     */
    public function persistUser(User $user)
    {
        $this->objectManager->persist($user);
        $this->objectManager->flush();

        return $user;
    }

    /**
     * Removes users from a csv file.
     */
    public function csvRemove($file)
    {
        $data = file_get_contents($file);
        $data = $this->container->get('claroline.utilities.misc')->formatCsvOutput($data);
        $usernames = str_getcsv($data, PHP_EOL);
        $this->objectManager->startFlushSuite();
        $i = 0;

        foreach ($usernames as $username) {
            $user = $this->getUserByUsername($username);

            if ($user) {
                $this->deleteUser($user);
                ++$i;
            }

            if ($i % 50 === 0) {
                $this->objectManager->forceFlush();
            }
        }

        $this->objectManager->endFlushSuite();
    }

    public function csvFacets($file)
    {
        $data = file_get_contents($file);
        $data = $this->container->get('claroline.utilities.misc')->formatCsvOutput($data);
        $lines = str_getcsv($data, PHP_EOL);
        $fields = array_shift($lines);
        $fields = str_getcsv($fields, ';');
        $facetManager = $this->container->get('claroline.manager.facet_manager');
        $this->objectManager->startFlushSuite();
        $i = 0;

        foreach ($lines as $line) {
            $values = str_getcsv($line, ';');
            $username = array_shift($values);
            $user = $this->getUserByUsername($username);

            foreach ($fields as $key => $field) {
                $fieldFacet = $facetManager->getFieldFacetByName($field);
                $facetManager->setFieldValue($user, $fieldFacet, $values[$key], true);
            }

            ++$i;

            if ($i % 100 === 0) {
                $this->objectManager->forceFlush();
                $this->objectManager->clear();
            }
        }

        $this->objectManager->endFlushSuite();
    }

    /**
     * Rename a user.
     * It renames the user role and its personal WS if needed.
     *
     * @param User   $user
     * @param string $previousUsername
     */
    public function rename(User $user, $previousUsername)
    {
        if ($user->getUsername() !== $previousUsername) {
            // Rename user role
            $userRole = $this->roleManager->getUserRole($previousUsername);
            if ($userRole) {
                $this->roleManager->renameUserRole($userRole, $user->getUsername());
            }

            // Rename personal WS
            $pws = $user->getPersonalWorkspace();
            if ($pws) {
                $personalWorkspaceName = $this->translator->trans('personal_workspace', [], 'platform').' '.$user->getUsername();
                $this->workspaceManager->rename($pws, trim($personalWorkspaceName));
            }
        }

        $this->objectManager->persist($user);
        $this->objectManager->flush();
    }

    public function setIsMailNotified(User $user, $isNotified)
    {
        $user->setIsMailNotified($isNotified);
        $this->objectManager->persist($user);
        $this->objectManager->flush();
    }

    /**
     * Removes a user.
     *
     * @param \Claroline\CoreBundle\Entity\User $user
     */
    public function deleteUser(User $user)
    {
        $this->log('Removing '.$user->getUsername().'...');
        /* When the api will identify a user, please uncomment this
        if ($this->container->get('security.token_storage')->getToken()->getUser()->getId() === $user->getId()) {
            throw new \Exception('A user cannot delete himself');
        }*/
        $userRole = $this->roleManager->getUserRole($user->getUsername());

        //soft delete~
        $user->setIsRemoved(true);
        $user->setMail('mail#'.$user->getId());
        $user->setFirstName('firstname#'.$user->getId());
        $user->setLastName('lastname#'.$user->getId());
        $user->setPlainPassword(uniqid());
        $user->setUsername('username#'.$user->getId());
        $user->setPublicUrl('removed#'.$user->getId());
        $user->setAdministrativeCode('code#'.$user->getId());
        $user->setIsEnabled(false);

        // keeping the user's workspace with its original code
        // would prevent creating a user with the same username
        // todo: workspace deletion should be an option
        $ws = $user->getPersonalWorkspace();

        if ($ws) {
            $ws->setCode($ws->getCode().'#deleted_user#'.$user->getId());
            $ws->setDisplayable(false);
            $this->objectManager->persist($ws);
        }

        if ($userRole) {
            $this->objectManager->remove($userRole);
        }
        $this->objectManager->persist($user);
        $this->objectManager->flush();

        $this->strictEventDispatcher->dispatch('claroline_users_delete', 'GenericData', [[$user]]);
        $this->strictEventDispatcher->dispatch('log', 'Log\LogUserDelete', [$user]);
        $this->strictEventDispatcher->dispatch('delete_user', 'DeleteUser', [$user]);
    }

    /**
     * Import users from an array.
     * There is the array format:.
     *
     * @todo some batch processing
     *
     * array(
     *     array(firstname, lastname, username, pwd, email, code, phone),
     *     array(firstname2, lastname2, username2, pwd2, email2, code2, phone2),
     *     array(firstname3, lastname3, username3, pwd3, email3, code3, phone3),
     * )
     *
     * @param array    $users
     * @param bool     $sendMail
     * @param \Closure $logger                 an anonymous function allowing to log actions
     * @param array    $additionalRoles
     * @param bool     $enableEmailNotifaction
     * @param array    $options
     *
     * @return array
     *
     * @throws AddRoleException
     * @throws \Claroline\CoreBundle\Persistence\NoFlushSuiteStartedException
     *
     * @internal param string $authentication an authentication source
     * @internal param bool $mail do the users need to be mailed
     */
    public function importUsers(
        array $users,
        $sendMail = true,
        $logger = null,
        $additionalRoles = [],
        $enableEmailNotifaction = false,
        $options = []
    ) {
        //build options
        if (!isset($options['ignore-update'])) {
            $options['ignore-update'] = false;
        }

        if (!isset($options['single-validate'])) {
            $options['single-validate'] = false;
        }

        // Return values
        $created = [];
        $updated = [];
        $skipped = [];
        // Skipped users table
        $skippedUsers = [];
        //keep these roles before the clear() will mess everything up. It's not what we want.
        $tmpRoles = $additionalRoles;
        $additionalRoles = [];
        //I need to do that to import roles from models. Please don't ask why, I have no fucking idea.
        $this->objectManager->clear();

        $roleUser = $this->roleManager->getRoleByName('ROLE_USER');
        $this->objectManager->merge($roleUser);
        $this->objectManager->persist($roleUser);

        foreach ($tmpRoles as $role) {
            if ($role) {
                $additionalRoles[] = $this->objectManager->merge($role);
            }
        }

        $max = $roleUser->getMaxUsers();
        $total = $this->countUsersByRoleIncludingGroup($roleUser);

        $countUsersToUpdate = $options['ignore-update'] ? 0 : $this->countUsersToUpdate($users);

        if ($total + count($users) - $countUsersToUpdate > $max) {
            throw new AddRoleException($total, count($users) - $countUsersToUpdate, $max);
        }

        $lg = $this->platformConfigHandler->getParameter('locale_language');
        $this->objectManager->startFlushSuite();
        $i = 1;
        $j = 0;
        $countCreated = 0;
        $countUpdated = 0;

        foreach ($users as $user) {
            $firstName = $user[0];
            $lastName = $user[1];
            $fullName = $firstName.' '.$lastName;
            $username = $user[2];
            $pwd = $user[3];
            $email = trim($user[4]);

            if (isset($user[5])) {
                $code = trim($user[5]) === '' ? null : $user[5];
            } else {
                $code = null;
            }

            if (isset($user[6])) {
                $phone = trim($user[6]) === '' ? null : $user[6];
            } else {
                $phone = null;
            }

            if (isset($user[7])) {
                $authentication = trim($user[7]) === '' ? null : $user[7];
            } else {
                $authentication = null;
            }

            if (isset($user[8])) {
                $modelName = trim($user[8]) === '' ? null : $user[8];
            } else {
                $modelName = null;
            }

            if (isset($user[9])) {
                $groupName = trim($user[9]) === '' ? null : $user[9];
            } else {
                $groupName = null;
            }

            if (isset($user[10])) {
                $organizationName = trim($user[10]) === '' ? null : $user[10];
            } else {
                $organizationName = null;
            }

            $hasPersonalWorkspace = (isset($user[11]) && !is_null($user[11]) && trim($user[11]) !== '') ?
                (bool) $user[11] : null;
            $isMailValidated = isset($user[12]) ? (bool) $user[12] : false;
            $isMailNotified = isset($user[13]) ? (bool) $user[13] : $enableEmailNotifaction;

            if ($modelName) {
                //TODO MODEL TEST
                $model = $this->objectManager
                    ->getRepository('Claroline\CoreBundle\Entity\Workspace\Workspace')
                    ->findOneByCode($modelName);
            } else {
                $model = null;
            }

            if ($organizationName) {
                $organizations = [$this->objectManager
                    ->getRepository('Claroline\CoreBundle\Entity\Organization\Organization')
                    ->findOneByName($organizationName), ];
            } else {
                $organizations = [];
            }

            if ($groupName) {
                $group = $this->groupManager->getGroupByNameAndScheduledForInsert($groupName);

                if (!$group) {
                    $group = new Group();
                    $group->setName($groupName);
                    $group = $this->groupManager->insertGroup($group);
                }
            } else {
                $group = null;
            }

            $userEntity = $this->getUserByUsernameOrMailOrCode($username, $email, $code);

            if ($userEntity && $options['ignore-update']) {
                if ($logger) {
                    $logger(" Skipping  {$userEntity->getUsername()}...");
                }
                $skipped[] = $fullName;
                continue;
            }

            $isNew = false;

            if (!$userEntity) {
                $isNew = true;
                $userEntity = new User();
                $userEntity->setPlainPassword($pwd);
                ++$countCreated;
            } else {
                if (!empty($pwd)) {
                    $userEntity->setPlainPassword($pwd);
                }
                ++$countUpdated;
            }

            $userEntity->setUsername($username);
            $userEntity->setMail($email);
            $userEntity->setFirstName($firstName);
            $userEntity->setLastName($lastName);
            $userEntity->setAdministrativeCode($code);
            $userEntity->setPhone($phone);
            $userEntity->setLocale($lg);
            $userEntity->setAuthentication($authentication);
            $userEntity->setIsMailNotified($isMailNotified);
            $userEntity->setIsMailValidated($isMailValidated);

            if ($options['single-validate']) {
                $errors = $this->validator->validate($userEntity);
                if (count($errors) > 0) {
                    $skippedUsers[$i] = $userEntity;
                    $skipped[] = $fullName;
                    if ($isNew) {
                        --$countCreated;
                    } else {
                        --$countUpdated;
                    }
                    continue;
                }
            }

            if (!$isNew && $logger) {
                $logger(" User $j ($username) being updated...");
                $this->roleManager->associateRoles($userEntity, $additionalRoles);
            }

            if ($isNew) {
                if ($logger) {
                    $logger(" User $j ($username) being created...");
                }

                $this->createUser(
                    $userEntity,
                    $sendMail,
                    $additionalRoles,
                    $model,
                    $username.uniqid(),
                    $organizations,
                    $hasPersonalWorkspace,
                    false
                );
            }

            $this->objectManager->persist($userEntity);
            if ($isNew) {
                $created[] = $fullName;
            } else {
                $updated[] = $fullName;
            }

            if ($group) {
                $this->groupManager->addUsersToGroup($group, [$userEntity]);
            }

            if ($logger) {
                $logger(' [UOW size: '.$this->objectManager->getUnitOfWork()->size().']');
            }
            ++$i;
            ++$j;

            if ($i % self::MAX_USER_BATCH_SIZE === 0) {
                if ($logger) {
                    $logger(' [UOW size: '.$this->objectManager->getUnitOfWork()->size().']');
                }

                $this->objectManager->forceFlush();

                if ($logger) {
                    $logger(' flushing users...');
                }

                $tmpRoles = $additionalRoles;
                $this->objectManager->clear();
                $additionalRoles = [];

                foreach ($tmpRoles as $toAdd) {
                    if ($toAdd) {
                        $additionalRoles[] = $this->objectManager->merge($toAdd);
                    }
                }

                if ($this->tokenStorage->getToken()) {
                    $this->objectManager->merge($this->tokenStorage->getToken()->getUser());
                }
            }
        }

        $this->objectManager->endFlushSuite();
        if ($logger) {
            $logger($countCreated.' users created.');
            $logger($countUpdated.' users updated.');
        }

        if ($logger) {
            $logger($countCreated.' users created.');
            $logger($countUpdated.' users updated.');
        }

        foreach ($skippedUsers as $key => $user) {
            $logger('The user '.$user.' was skipped at line '.$key.' because it failed the validation pass.');
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
        ];
    }

    /**
     * Creates the personal workspace of a user.
     *
     * @param \Claroline\CoreBundle\Entity\User                $user
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $model
     */
    public function setPersonalWorkspace(User $user, Workspace $model = null)
    {
        $locale = $this->platformConfigHandler->getParameter('locale_language');
        $this->translator->setLocale($locale);
        $created = $this->workspaceManager->getWorkspaceByCode($user->getUsername());

        if (count($created) > 0) {
            $code = $user->getUsername().'~'.uniqid();
        } else {
            $code = $user->getUsername();
        }

        $personalWorkspaceName = $this->translator->trans('personal_workspace', [], 'platform').' - '.$user->getUsername();
        $workspace = new Workspace();
        $workspace->setCode($code);
        $workspace->setName($personalWorkspaceName);
        $workspace->setCreator($user);
        $workspace = !$model ?
            $this->workspaceManager->copy($this->workspaceManager->getDefaultModel(true), $workspace) :
            $this->workspaceManager->copy($model, $workspace);

        //add "my public documents" folder
        $resourceManager = $this->container->get('claroline.manager.resource_manager');
        //TODO MODEL
        $resourceManager->addPublicFileDirectory($workspace);
        $workspace->setIsPersonal(true);
        $user->setPersonalWorkspace($workspace);
        $this->objectManager->persist($user);
        $this->objectManager->flush();
    }

    public function countUsersToUpdate(array $users)
    {
        $count = 0;

        foreach ($users as $user) {
            if (isset($user[5])) {
                $code = trim($user[5]) === '' ? null : $user[5];
            } else {
                $code = null;
            }

            $userEntity = $this->getUserByUsernameOrMailOrCode($user[2], $user[4], $code);
            if ($userEntity) {
                ++$count;
            }
        }

        return $count;
    }

    /**
     * Sets an array of platform role to a user.
     *
     * @param \Claroline\CoreBundle\Entity\User $user
     * @param ArrayCollection                   $roles
     */
    public function setPlatformRoles(User $user, $roles)
    {
        $this->roleManager->resetRoles($user);
        $this->roleManager->associateRoles($user, $roles);
    }

    /**
     * Serialize a user. Use JMS serializer from entities instead.
     *
     * @param array $users
     *
     * @return array
     *
     * @deprecated
     */
    public function convertUsersToArray(array $users)
    {
        $content = [];
        $i = 0;

        foreach ($users as $user) {
            $content[$i]['id'] = $user->getId();
            $content[$i]['username'] = $user->getUsername();
            $content[$i]['lastname'] = $user->getLastName();
            $content[$i]['firstname'] = $user->getFirstName();
            $content[$i]['administrativeCode'] = $user->getAdministrativeCode();

            $rolesString = '';
            $roles = $user->getEntityRoles();
            $rolesCount = count($roles);
            $j = 0;

            foreach ($roles as $role) {
                $rolesString .= "{$this->translator->trans($role->getTranslationKey(), [], 'platform')}";

                if ($j < $rolesCount - 1) {
                    $rolesString .= ' ,';
                }
                ++$j;
            }
            $content[$i]['roles'] = $rolesString;
            ++$i;
        }

        return $content;
    }

    /**
     * @param type $username
     *
     * @return User
     */
    public function getUserByUsername($username)
    {
        try {
            $user = $this->userRepo->loadUserByUsername($username);
        } catch (\Exception $e) {
            $user = null;
        }

        return $user;
    }

    /**
     * @param \Symfony\Component\Security\Core\User\UserInterface $user
     *
     * @return User
     */
    public function refreshUser(UserInterface $user)
    {
        return $this->userRepo->refreshUser($user);
    }

    /**
     * @param int    $page
     * @param int    $max
     * @param string $orderedBy
     * @param string $order
     *
     * @return \Pagerfanta\Pagerfanta;
     */
    public function getAllUsers($page, $max = 20, $orderedBy = 'id', $order = null)
    {
        $query = $this->userRepo->findAll(false, $orderedBy, $order);

        return $this->pagerFactory->createPager($query, $page, $max);
    }

    public function getAll()
    {
        return $this->userRepo->findAll();
    }

    /**
     * @param string $search
     * @param int    $page
     * @param int    $max
     * @param string $orderedBy
     *
     * @return \Pagerfanta\Pagerfanta;
     */
    public function getUsersByName($search, $page, $max = 20, $orderedBy = 'id')
    {
        $query = $this->userRepo->findByName($search, false, $orderedBy);

        return $this->pagerFactory->createPager($query, $page, $max);
    }

    /**
     * @param string $firstName
     * @param string $lastName
     *
     * @return User[]
     */
    public function getUsersByFirstNameAndLastName($firstName, $lastName)
    {
        return $this->userRepo->findBy([
            'firstName' => $firstName,
            'lastName' => $lastName,
        ]);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Group $group
     * @param int                                $page
     * @param int                                $max
     * @param string                             $orderedBy
     *
     * @return \Pagerfanta\Pagerfanta;
     */
    public function getUsersByGroup(
        Group $group,
        $page,
        $max = 20,
        $orderedBy = 'id',
        $order = 'ASC'
    ) {
        $query = $this->userRepo->findByGroup($group, false, $orderedBy, $order);

        return $this->pagerFactory->createPager($query, $page, $max);
    }

    /**
     * @param string                             $search
     * @param \Claroline\CoreBundle\Entity\Group $group
     * @param int                                $page
     * @param int                                $max
     * @param string                             $orderedBy
     *
     * @return \Pagerfanta\Pagerfanta
     */
    public function getUsersByNameAndGroup(
        $search,
        Group $group,
        $page,
        $max = 20,
        $orderedBy = 'id',
        $order = 'ASC'
    ) {
        $query = $this->userRepo->findByNameAndGroup(
            $search,
            $group,
            false,
            $orderedBy,
            $order
        );

        return $this->pagerFactory->createPager($query, $page, $max);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace[] $workspaces
     * @param int                                                $page
     * @param int                                                $max
     *
     * @return \Pagerfanta\Pagerfanta
     */
    public function getUsersByWorkspaces(array $workspaces, $page = 1, $max = 20, $withPager = true)
    {
        if ($withPager) {
            $query = $this->userRepo->findUsersByWorkspaces($workspaces, false);

            return $this->pagerFactory->createPager($query, $page, $max);
        } else {
            return  $this->userRepo->findUsersByWorkspaces($workspaces);
        }
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @param string                                           $search
     * @param int                                              $page
     * @param int                                              $max
     *
     * @return \Pagerfanta\Pagerfanta
     */
    public function getAllUsersByWorkspaceAndName(Workspace $workspace, $search, $page, $max = 20)
    {
        $query = $this->userRepo->findAllByWorkspaceAndName($workspace, $search, false);

        return $this->pagerFactory->createPager($query, $page, $max);
    }

    /**
     * @return int
     */
    public function getNbUsers()
    {
        return $this->userRepo->count();
    }

    public function countUsersForPlatformRoles()
    {
        $roles = $this->roleManager->getAllPlatformRoles();
        $usersInRoles = [];
        $usersInRoles['user_accounts'] = 0;
        foreach ($roles as $role) {
            $restrictionRoleNames = null;
            if ($role->getName() === 'ROLE_USER') {
                $restrictionRoleNames = ['ROLE_WS_CREATOR', 'ROLE_ADMIN'];
            } elseif ($role->getName() === 'ROLE_WS_CREATOR') {
                $restrictionRoleNames = ['ROLE_ADMIN'];
            }
            $usersInRoles[$role->getTranslationKey()] = intval(
                $this->userRepo->countUsersByRole($role, $restrictionRoleNames)
            );
            $usersInRoles['user_accounts'] = $this->userRepo->countUsers();
        }

        return $usersInRoles;
    }

    /**
     * @param int[] $ids
     *
     * @return User[]
     */
    public function getUsersByIds(array $ids)
    {
        return $this->objectManager->findByIds('Claroline\CoreBundle\Entity\User', $ids);
    }

    /**
     * @param string $guid
     *
     * @return \Claroline\CoreBundle\Entity\Workspace\Workspace
     */
    public function getOneByGuid($guid)
    {
        return $this->userRepo->findOneByGuid($guid);
    }

    /**
     * @param int $max
     *
     * @return User[]
     */
    public function getUsersEnrolledInMostWorkspaces($max)
    {
        return $this->userRepo->findUsersEnrolledInMostWorkspaces($max);
    }

    /**
     * @param int $max
     *
     * @return User[]
     */
    public function getUsersOwnersOfMostWorkspaces($max)
    {
        return $this->userRepo->findUsersOwnersOfMostWorkspaces($max);
    }

    /**
     * @param int $userId
     *
     * @return User
     */
    public function getUserById($userId)
    {
        return $this->userRepo->find($userId);
    }

    /**
     * @param Role[] $roles
     * @param int    $page
     * @param int    $max
     * @param string $orderedBy
     * @param null   $order
     *
     * @return \Pagerfanta\Pagerfanta
     */
    public function getByRolesIncludingGroups(array $roles, $page = 1, $max = 20, $orderedBy = 'id', $order = null)
    {
        $res = $this->userRepo->findByRolesIncludingGroups($roles, true, $orderedBy, $order);

        return $this->pagerFactory->createPager($res, $page, $max);
    }

    /**
     * @param Role[] $roles
     * @param int    $page
     * @param int    $max
     *
     * @return \Pagerfanta\Pagerfanta
     */
    public function getUsersByRolesIncludingGroups(
        array $roles, $page = 1,
        $max = 20,
        $executeQuery = true
    ) {
        $users = $this->userRepo->findUsersByRolesIncludingGroups($roles, $executeQuery);

        if (!$executeQuery) {
            return $users;
        }

        return $this->pagerFactory->createPagerFromArray($users, $page, $max);
    }

    /*
     * I don't want to break the old pager wich is oddly written
     */
    public function getUsersByRolesWithGroups(array $roles)
    {
        return $this->userRepo->findUsersByRolesIncludingGroups($roles, true);
    }

    public function getUsersExcudingRoles(array $roles, $offet = null, $limit = null)
    {
        return $this->userRepo->findUsersExcludingRoles($roles, $offet, $limit);
    }

    /**
     * @param Role[] $roles
     * @param string $search
     * @param int    $page
     * @param int    $max
     * @param string $orderedBy
     *
     * @return \Pagerfanta\Pagerfanta
     */
    public function getByRolesAndNameIncludingGroups(array $roles, $search, $page = 1, $max = 20, $orderedBy = 'id', $direction = null)
    {
        $res = $this->userRepo->findByRolesAndNameIncludingGroups($roles, $search, true, $orderedBy, $direction);

        return $this->pagerFactory->createPager($res, $page, $max);
    }

    /**
     * @param Role[] $roles
     * @param int    $page
     * @param int    $max
     *
     * @return \Pagerfanta\Pagerfanta
     */
    public function getUsersByRoles(array $roles, $page = 1, $max = 20)
    {
        $res = $this->userRepo->findByRoles($roles, true);

        return $this->pagerFactory->createPager($res, $page, $max);
    }

    /**
     * @param string $email
     *
     * @return User
     */
    public function getUserByEmail($email)
    {
        return $this->userRepo->findOneByMail($email);
    }

    /**
     * @todo Please describe me. I couldn't find findOneByResetPasswordHash
     *
     * @param string $resetPassword
     *
     * @return User
     */
    public function getByResetPasswordHash($resetPassword)
    {
        return $this->userRepo->findOneByResetPasswordHash($resetPassword);
    }

    /**
     * @param string $validationHash
     *
     * @return User
     */
    public function getByEmailValidationHash($validationHash)
    {
        return $this->userRepo->findByEmailValidationHash($validationHash);
    }

    public function validateEmailHash($validationHash)
    {
        $users = $this->getByEmailValidationHash($validationHash);
        $user = $users[0];
        $user->setIsMailValidated(true);
        $this->objectManager->persist($user);
        $this->objectManager->flush();
    }

    /**
     * @return User[]
     */
    public function getAllEnabledUsers($executeQuery = true)
    {
        return $this->userRepo->findAllEnabledUsers($executeQuery);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\User $user
     */
    public function uploadAvatar(User $user)
    {
        if ($user->getPictureFile()) {
            $file = $user->getPictureFile();
            $publicFile = $this->fu->createFile($file, $file->getBasename());
            $this->fu->createFileUse($publicFile, get_class($user), $user->getGuid());
            //../.. for legacy compatibility
            $user->setPicture('../../'.$publicFile->getUrl());
            $this->objectManager->persist($user);
            $this->objectManager->flush();
        }
    }

    /**
     * Set the user locale.
     *
     * @param \Claroline\CoreBundle\Entity\User $user
     * @param string                            $locale Language with format en, fr, es, etc
     */
    public function setLocale(User $user, $locale = 'en')
    {
        $user->setLocale($locale);
        $this->objectManager->persist($user);
        $this->objectManager->flush();
    }

    public function toArrayForPicker($users)
    {
        $resultArray = [];

        $resultArray['users'] = [];
        if (count($users) > 0) {
            foreach ($users as $user) {
                $userArray = [];
                $userArray['id'] = $user->getId();
                $userArray['name'] = $user->getFirstName().' '.$user->getLastName();
                $userArray['mail'] = $user->getMail();
                $userArray['avatar'] = $user->getPicture();
                array_push($resultArray['users'], $userArray);
            }
        }

        return $resultArray;
    }

    /**
     * @param User $user
     * @param int  $try
     *
     * @return string
     */
    public function generatePublicUrl(User $user)
    {
        $publicUrl = $user->getFirstName().'.'.$user->getLastName();
        $publicUrl = strtolower(str_replace(' ', '-', $publicUrl));
        $searchedUsers = $this->objectManager->getRepository('ClarolineCoreBundle:User')->findOneByPublicUrl($publicUrl);

        if (null !== $searchedUsers) {
            $publicUrl .= '_'.uniqid();
        }

        return $publicUrl;
    }

    public function countUsersByRoleIncludingGroup(Role $role)
    {
        return $this->objectManager->getRepository('ClarolineCoreBundle:User')->countUsersByRoleIncludingGroup($role);
    }

    public function countUsersOfGroup(Group $group)
    {
        return $this->userRepo->countUsersOfGroup($group);
    }

    public function setUserInitDate(User $user)
    {
        $accountDuration = $this->platformConfigHandler->getParameter('account_duration');
        $expirationDate = new \DateTime();
        $expirationYear = (strtotime('2100-01-01')) ? 2100 : 2038;

        ($accountDuration === null) ?
            $expirationDate->setDate($expirationYear, 1, 1) :
            $expirationDate->add(new \DateInterval('P'.$accountDuration.'D'));

        $user->setExpirationDate($expirationDate);
        $user->setInitDate(new \DateTime());
        $this->objectManager->persist($user);
        $this->objectManager->flush();
    }

    public function getUsersWithoutUserRole($executeQuery = true)
    {
        return $this->userRepo->findUsersWithoutUserRole($executeQuery);
    }

    public function getUsersWithRights(
        ResourceNode $node,
        $orderedBy = 'firstName',
        $order = 'ASC',
        $page = 1,
        $max = 50,
        $executeQuery = true
    ) {
        $users = $this->userRepo
            ->findUsersWithRights($node, $orderedBy, $order, $executeQuery);

        return $executeQuery ?
            $this->pagerFactory->createPagerFromArray($users, $page, $max) :
            $this->pagerFactory->createPager($users, $page, $max);
    }

    public function getUsersWithoutRights(
        ResourceNode $node,
        $orderedBy = 'firstName',
        $order = 'ASC',
        $page = 1,
        $max = 50,
        $executeQuery = true
    ) {
        $users = $this->userRepo
            ->findUsersWithoutRights($node, $orderedBy, $order, $executeQuery);

        return $executeQuery ?
            $this->pagerFactory->createPagerFromArray($users, $page, $max) :
            $this->pagerFactory->createPager($users, $page, $max);
    }

    public function getSearchedUsersWithRights(
        ResourceNode $node,
        $search = '',
        $orderedBy = 'firstName',
        $order = 'ASC',
        $page = 1,
        $max = 50,
        $executeQuery = true
    ) {
        $users = $this->userRepo->findSearchedUsersWithRights(
            $node,
            $search,
            $orderedBy,
            $order,
            $executeQuery
        );

        return $executeQuery ?
            $this->pagerFactory->createPagerFromArray($users, $page, $max) :
            $this->pagerFactory->createPager($users, $page, $max);
    }

    public function getSearchedUsersWithoutRights(
        ResourceNode $node,
        $search = '',
        $orderedBy = 'firstName',
        $order = 'ASC',
        $page = 1,
        $max = 50,
        $executeQuery = true
    ) {
        $users = $this->userRepo->findSearchedUsersWithoutRights(
            $node,
            $search,
            $orderedBy,
            $order,
            $executeQuery
        );

        return $executeQuery ?
            $this->pagerFactory->createPagerFromArray($users, $page, $max) :
            $this->pagerFactory->createPager($users, $page, $max);
    }

    public function getOneUserByUsername($username, $executeQuery = true)
    {
        return $this->userRepo->findOneUserByUsername($username, $executeQuery);
    }

    public function getUserByUsernameOrMail($username, $mail, $executeQuery = true)
    {
        return $this->userRepo->findUserByUsernameOrMail(
            $username,
            $mail,
            $executeQuery
        );
    }

    public function getUsersByUsernamesOrMails($usernames, $mails, $executeQuery = true)
    {
        return $this->userRepo->findUsersByUsernamesOrMails($usernames, $mails, $executeQuery);
    }

    public function getUserByUsernameOrMailOrCode($username, $mail, $code)
    {
        if (empty($code) || !$this->platformConfigHandler->getParameter('is_user_admin_code_unique')) {
            return $this->getUserByUsernameOrMail($username, $mail, true);
        }

        return $this->userRepo->findUserByUsernameOrMailOrCode($username, $mail, $code);
    }

    public function getUserByUsernameAndMail($username, $mail, $executeQuery = true)
    {
        return $this->userRepo->findUserByUsernameAndMail(
            $username,
            $mail,
            $executeQuery
        );
    }

    public function getCountAllEnabledUsers($executeQuery = true)
    {
        return $this->userRepo->countAllEnabledUsers($executeQuery);
    }

    public function importPictureFiles($filepath)
    {
        $archive = new \ZipArchive();
        $archive->open($filepath);
        $tmpDir = $this->platformConfigHandler->getParameter('tmp_dir').DIRECTORY_SEPARATOR.uniqid();
        //add the tmp dir to the "trash list files"
        $tmpList = $this->container->getParameter('claroline.param.platform_generated_archive_path');
        file_put_contents($tmpList, $tmpDir."\n", FILE_APPEND);
        $archive->extractTo($tmpDir);
        $iterator = new \DirectoryIterator($tmpDir);

        foreach ($iterator as $element) {
            if (!$element->isDot()) {
                $fileName = basename($element->getPathName());
                $username = preg_replace("/\.[^.]+$/", '', $fileName);
                $user = $this->getUserByUsername($username);
                $file = new File($element->getPathName());

                $publicFile = $this->fu->createFile($file, $file->getBasename());
                $this->fu->createFileUse($publicFile, get_class($user), $user->getGuid());
                //../.. for legacy compatibility
                $user->setPicture('../../'.$publicFile->getUrl());
                $this->objectManager->persist($user);
            }
        }

        $this->objectManager->flush();
    }

    /**
     * Checks if a user will have a personal workspace at his creation.
     */
    private function personalWorkspaceAllowed($roles)
    {
        $roles[] = $this->roleManager->getRoleByName('ROLE_USER');

        foreach ($roles as $role) {
            if ($role->isPersonalWorkspaceCreationEnabled()) {
                return true;
            }
        }

        return false;
    }

    public function countByRoles(array $roles, $includeGrps = true)
    {
        return $this->userRepo->countByRoles($roles, $includeGrps);
    }

    /**
     * Activates a User and set the init date to now.
     */
    public function activateUser(User $user)
    {
        $user->setIsEnabled(true);
        $user->setIsMailValidated(true);
        $user->setResetPasswordHash(null);
        $user->setInitDate(new \DateTime());
        $this->objectManager->persist($user);
        $this->objectManager->flush();
    }

    /**
     * Logs the current user.
     */
    public function logUser(User $user)
    {
        $this->strictEventDispatcher->dispatch('log', 'Log\LogUserLogin', [$user]);
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->tokenStorage->setToken($token);
    }

    public function persistUserOptions(UserOptions $options)
    {
        $this->objectManager->persist($options);
        $this->objectManager->flush();
    }

    public function getUserOptions(User $user)
    {
        $options = $user->getOptions();

        if (is_null($options)) {
            $options = new UserOptions();
            $options->setUser($user);
            $this->objectManager->persist($options);
            $user->setOptions($options);
            $this->objectManager->persist($user);
            $this->objectManager->flush();
        }

        return $options;
    }

    public function switchDesktopMode(User $user)
    {
        $options = $this->getUserOptions($user);
        $mode = $options->getDesktopMode();

        if ($mode === UserOptions::READ_ONLY_MODE) {
            $options->setDesktopMode(UserOptions::EDITION_MODE);
        } else {
            $options->setDesktopMode(UserOptions::READ_ONLY_MODE);
        }
        $this->persistUserOptions($options);

        return $options;
    }

    public function getUsersForUserPicker(
        User $user,
        $search = '',
        $withAllUsers = false,
        $withUsername = true,
        $withMail = false,
        $withCode = false,
        $page = 1,
        $max = 50,
        $orderedBy = 'lastName',
        $order = 'ASC',
        array $searchedWorkspaces = [],
        array $searchedRoles = [],
        array $searchedGroups = [],
        array $excludedUsers = [],
        array $forcedUsers = [],
        array $forcedGroups = [],
        array $forcedRoles = [],
        array $forcedWorkspaces = [],
        $withAdminOrgas = false
    ) {
        if (count($searchedRoles) > 0 ||
            count($searchedGroups) > 0 ||
            count($searchedWorkspaces) > 0) {
            $roles = $searchedRoles;
            $groups = $searchedGroups;
            $workspaces = $searchedWorkspaces;
        } else {
            $roles = $withAllUsers ?
                [] :
                $this->generateRoleRestrictions($user);
            $groups = $withAllUsers ?
                [] :
                $this->generateGroupRestrictions($user);
            $workspaces = $withAllUsers ?
                [] :
                $this->generateWorkspaceRestrictions($user);
        }
        $withOrgas = !$user->hasRole('ROLE_ADMIN') && !$withAllUsers && $withAdminOrgas;
        $forcedOrganizations = $withOrgas ? $user->getAdministratedOrganizations()->toArray() : [];

        $users = $this->userRepo->findUsersForUserPicker(
            $search,
            $withUsername,
            $withMail,
            $withCode,
            $orderedBy,
            $order,
            $roles,
            $groups,
            $workspaces,
            $excludedUsers,
            $forcedUsers,
            $forcedGroups,
            $forcedRoles,
            $forcedWorkspaces,
            $withOrgas,
            $forcedOrganizations
        );

        return $this->pagerFactory->createPagerFromArray($users, $page, $max);
    }

    public function getAllVisibleUsersIdsForUserPicker(User $user)
    {
        $usersIds = [];
        $roles = $this->generateRoleRestrictions($user);
        $groups = $this->generateGroupRestrictions($user);
        $workspaces = $this->generateWorkspaceRestrictions($user);
        $users = $this->userRepo->findUsersForUserPicker(
            '',
            false,
            false,
            false,
            'lastName',
            'ASC',
            $roles,
            $groups,
            $workspaces
        );

        foreach ($users as $user) {
            $usersIds[] = $user->getId();
        }

        return $usersIds;
    }

    private function generateRoleRestrictions(User $user)
    {
        $restrictions = [];

        if (!$user->hasRole('ROLE_ADMIN')) {
            $wsRoles = $this->roleManager->getWorkspaceRolesByUser($user);

            foreach ($wsRoles as $wsRole) {
                $wsRoleId = $wsRole->getId();
                $workspace = $wsRole->getWorkspace();
                $guid = $workspace->getGuid();
                $managerRoleName = 'ROLE_WS_MANAGER_'.$guid;

                if ($wsRole->getName() === $managerRoleName) {
                    $workspaceRoles = $this->roleManager->getWorkspaceRoles($workspace);

                    foreach ($workspaceRoles as $workspaceRole) {
                        $workspaceRoleId = $workspaceRole->getId();

                        if (!isset($restrictions[$workspaceRoleId])) {
                            $restrictions[$workspaceRoleId] = $workspaceRole;
                        }
                    }
                } elseif (!isset($restrictions[$wsRoleId])) {
                    $restrictions[$wsRoleId] = $wsRole;
                }
            }
        }

        return $restrictions;
    }

    private function generateGroupRestrictions(User $user)
    {
        $restrictions = [];

        if (!$user->hasRole('ROLE_ADMIN')) {
            $restrictions = $user->getGroups()->toArray();
        }

        return $restrictions;
    }

    private function generateWorkspaceRestrictions(User $user)
    {
        $restrictions = [];

        if (!$user->hasRole('ROLE_ADMIN')) {
            $restrictions = $this->workspaceManager->getWorkspacesByUser($user);
        }

        return $restrictions;
    }

    public function initializePassword(User $user)
    {
        $user->setHashTime(time());
        $password = sha1(rand(1000, 10000).$user->getUsername().$user->getSalt());
        $user->setResetPasswordHash($password);
        $this->objectManager->persist($user);
        $this->objectManager->flush();
    }

    public function hideEmailValidation(User $user)
    {
        $user->setHideMailWarning(true);
        $this->objectManager->persist($user);
        $this->objectManager->flush();
    }

    /**
     * Big user search method ! hell yeah !
     */
    public function searchPartialList($searches, $page, $limit, $count = false)
    {
        $baseFieldsName = User::getUserSearchableFields();
        $facetFields = $this->objectManager->getRepository('ClarolineCoreBundle:Facet\FieldFacet')->findAll();
        $facetFieldsName = [];

        foreach ($facetFields as $facetField) {
            $facetFieldsName[] = $facetField->getName();
        }

        $qb = $this->objectManager->createQueryBuilder();
        $count ? $qb->select('count(u)') : $qb->select('u');
        $qb->from('Claroline\CoreBundle\Entity\User', 'u')
            ->where('u.isRemoved = false');

        //Admin can see everything, but the others... well they can only see their own organizations.
        if (!$this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            $currentUser = $this->tokenStorage->getToken()->getUser();
            $qb->leftJoin('u.organizations', 'uo');
            $qb->leftJoin('uo.administrators', 'ua');
            $qb->leftJoin('u.groups', 'ug');
            $qb->leftJoin('ug.organizations', 'go');
            $qb->leftJoin('go.administrators', 'ga');
            $qb->andWhere(
              $qb->expr()->orX(
                $qb->expr()->eq('ua.id', ':userId'),
                $qb->expr()->eq('ga.id', ':userId')
              )
            );

            $qb->setParameter('userId', $currentUser->getId());
        }

        foreach ($searches as $key => $search) {
            foreach ($search as $id => $el) {
                if (in_array($key, $baseFieldsName)) {
                    $qb->andWhere("UPPER (u.{$key}) LIKE :{$key}{$id}");
                    $qb->setParameter($key.$id, '%'.strtoupper($el).'%');
                } elseif (in_array($key, $facetFieldsName)) {
                    $qb->join('u.fieldsFacetValue', "ffv{$id}");
                    $qb->join("ffv{$id}.fieldFacet", "f{$id}");
                    $qb->andWhere("UPPER (ffv{$id}.stringValue) LIKE :{$key}{$id}");
                    $qb->orWhere("ffv{$id}.floatValue = :{$key}{$id}");
                    $qb->andWhere("f{$id}.name LIKE :facet{$id}");
                    $qb->setParameter($key.$id, '%'.strtoupper($el).'%');
                    $qb->setParameter("facet{$id}", $key);
                } elseif ($key === 'group_name') {
                    $qb->join('u.groups', "g{$id}");
                    $qb->andWhere("UPPER (g{$id}.name) LIKE :{$key}{$id}");
                    $qb->setParameter($key.$id, '%'.strtoupper($el).'%');
                }
                if ($key === 'group_id') {
                    $qb->join('u.groups', "g{$id}");
                    $qb->andWhere("g{$id}.id = :{$key}{$id}");
                    $qb->setParameter($key.$id, $el);
                }
                if ($key === 'organization_name') {
                    $qb->join('u.organizations', "o{$id}");
                    $qb->andWhere("UPPER (o{$id}.name) LIKE :{$key}{$id}");
                    $qb->setParameter($key.$id, '%'.strtoupper($el).'%');
                }
                if ($key === 'organization_id') {
                    $qb->join('u.organizations', "o{$id}");
                    $qb->andWhere('o{$id}.id = :id');
                    $qb->setParameter($key.$id, $el);
                }
                if ($key === 'name') {
                    $qb->andWhere(
                      $qb->expr()->orX(
                          $qb->expr()->like('u.username', ":{$key}{$id}"),
                          $qb->expr()->like('u.lastName', ":{$key}{$id}"),
                          $qb->expr()->like('u.firstName', ":{$key}{$id}"),
                          $qb->expr()->like('u.administrativeCode', ":{$key}{$id}"),
                          $qb->expr()->like('u.mail', ":{$key}{$id}")
                      )
                    );

                    $qb->setParameter($key.$id, "%$el%");
                }
            }
        }

        $this->strictEventDispatcher->dispatch(
            'user_edit_search_event',
            'UserEditSearch',
            [$qb]
        );

        $query = $qb->getQuery();

        if ($page !== null && $limit !== null && !$count) {
            $query->setMaxResults($limit);
            $query->setFirstResult($page * $limit);
        }

        return $count ? $query->getSingleScalarResult() : $query->getResult();
    }

    public function getUserSearchableFields()
    {
        $fields = $this->container->get('claroline.manager.facet_manager')->getFieldFacets();

        $baseFields = User::getSearchableFields();

        foreach ($fields as $field) {
            $baseFields[] = $field->getName();
        }

        $baseFields[] = 'group_name';
        $baseFields[] = 'organization_name';

        $event = $this->strictEventDispatcher->dispatch(
            'user_add_filter_event',
            'UserAddFilter',
            [$baseFields]
        );

        return $event->getFilters();
    }

    /**
     * This method will bind each users who don't already have an organization to the default one.
     */
    public function bindUserToOrganization()
    {
        $limit = 250;
        $offset = 0;
        $this->log('Add organizations to users...');
        $this->objectManager->startFlushSuite();
        $countUsers = $this->objectManager->count('ClarolineCoreBundle:User');
        $default = $this->organizationManager->getDefault();
        $i = 0;

        while ($offset < $countUsers) {
            $users = $this->userRepo->findBy([], null, $limit, $offset);

            foreach ($users as $user) {
                if (count($user->getOrganizations()) === 0) {
                    ++$i;
                    $this->log('Add default organization for user '.$user->getUsername());
                    $user->addOrganization($default);
                    $this->objectManager->persist($user);

                    if ($i % 250 === 0) {
                        $this->log("Flushing... [UOW = {$this->objectManager->getUnitOfWork()->size()}]");
                        $this->objectManager->forceFlush();
                    }
                } else {
                    $this->log("Organization for user {$user->getUsername()} already exists");
                }
            }

            $this->log("Flushing... [UOW = {$this->objectManager->getUnitOfWork()->size()}]");
            $this->objectManager->forceFlush();
            $default = $this->organizationManager->getDefault();

            $offset += $limit;
        }

        $this->objectManager->endFlushSuite();
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param string $search
     * @param int    $page
     * @param int    $max
     *
     * @return \Pagerfanta\Pagerfanta;
     */
    public function getAllUsersBySearch($page, $search, $max = 20)
    {
        $users = $this->userRepo->findAllUserBySearch($search);

        return $this->pagerFactory->createPagerFromArray($users, $page, $max);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Group $group
     *
     * @return User[]
     */
    public function getUsersByGroupWithoutPager(Group $group)
    {
        return $this->userRepo->findByGroup($group);
    }

    /**
     * @param Workspace $workspace
     *
     * @return User[]
     */
    public function getByWorkspaceWithUsersFromGroup(Workspace $workspace)
    {
        return $this->userRepo->findByWorkspaceWithUsersFromGroup($workspace);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace[] $workspaces
     * @param int                                                $page
     * @param string                                             $search
     * @param int                                                $max
     *
     * @return \Pagerfanta\Pagerfanta
     */
    public function getUsersByWorkspacesAndSearch(
        array $workspaces,
        $page,
        $search,
        $max = 20
    ) {
        $users = $this->userRepo
            ->findUsersByWorkspacesAndSearch($workspaces, $search);

        return $this->pagerFactory->createPagerFromArray($users, $page, $max);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Group $group
     * @param int                                $page
     * @param int                                $max
     * @param string                             $orderedBy
     *
     * @return \Pagerfanta\Pagerfanta
     */
    public function getGroupOutsiders(Group $group, $page, $max = 20, $orderedBy = 'id')
    {
        $query = $this->userRepo->findGroupOutsiders($group, false, $orderedBy);

        return $this->pagerFactory->createPager($query, $page, $max);
    }
    /**
     * @param \Claroline\CoreBundle\Entity\Group $group
     * @param int                                $page
     * @param string                             $search
     * @param int                                $max
     * @param string                             $orderedBy
     *
     * @return \Pagerfanta\Pagerfanta
     */
    public function getGroupOutsidersByName(Group $group, $page, $search, $max = 20, $orderedBy = 'id')
    {
        $query = $this->userRepo->findGroupOutsidersByName($group, $search, false, $orderedBy);

        return $this->pagerFactory->createPager($query, $page, $max);
    }

    public function getResourceManagerDisplayMode($index)
    {
        $displayMode = 'default';
        $user = $this->tokenStorage->getToken()->getUser();

        if ($user !== 'anon.') {
            $options = $this->getUserOptions($user);
            $details = $options->getDetails();

            if (!is_null($details) && isset($details['resourceManagerDisplayMode']) && isset($details['resourceManagerDisplayMode'][$index])) {
                $displayMode = $details['resourceManagerDisplayMode'][$index];
            }
        }

        return $displayMode;
    }

    public function registerResourceManagerDisplayModeByUser(User $user, $index, $displayMode)
    {
        $options = $this->getUserOptions($user);
        $details = $options->getDetails();

        if (is_null($details)) {
            $details = [];
        }

        if (!isset($details['resourceManagerDisplayMode'])) {
            $details['resourceManagerDisplayMode'] = [];
        }
        $details['resourceManagerDisplayMode'][$index] = $displayMode;
        $options->setDetails($details);
        $this->persistUserOptions($options);
    }

    public function enable(User $user)
    {
        $user->enable();
        $this->objectManager->persist($user);
        $this->objectManager->flush();

        return $user;
    }

    public function disable(User $user)
    {
        $user->disable();
        $this->objectManager->persist($user);
        $this->objectManager->flush();

        return $user;
    }

    public function getDefaultUser()
    {
        $user = $this->getUserByUsername('claroline-connect');

        if (!$user) {
            $user = new User();
            $user->setUsername('claroline-connect');
            $user->setFirstName('claroline-connect');
            $user->setLastName('claroline-connect');
            $user->setMail('claroline-connect');
            $user->setPlainPassword(uniqid('', true));
            $user->disable();
            $user->remove();
            $this->createUser($user, false, [], null, null, [], false, false);
        }

        return $user;
    }
}
