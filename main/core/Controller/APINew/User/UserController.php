<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\APINew\User;

use Claroline\AppBundle\Annotations\ApiDoc;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Controller\APINew\Model\HasGroupsTrait;
use Claroline\CoreBundle\Controller\APINew\Model\HasOrganizationsTrait;
use Claroline\CoreBundle\Controller\APINew\Model\HasRolesTrait;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\User\MergeUsersEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Configuration\PlatformDefaults;
use Claroline\CoreBundle\Manager\MailManager;
use Claroline\CoreBundle\Manager\RegistrationManager;
use Claroline\CoreBundle\Manager\UserManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @EXT\Route("/user")
 */
class UserController extends AbstractCrudController
{
    use HasRolesTrait;
    use HasOrganizationsTrait;
    use HasGroupsTrait;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var AuthorizationCheckerInterface */
    private $authChecker;

    /** @var StrictDispatcher */
    private $eventDispatcher;

    /** @var PlatformConfigurationHandler */
    private $config;

    /** @var UserManager */
    private $manager;

    /** @var MailManager */
    private $mailManager;

    /** @var RegistrationManager */
    private $registrationManager;

    /**
     * UserController constructor.
     *
     * @param TokenStorageInterface         $tokenStorage
     * @param AuthorizationCheckerInterface $authChecker
     * @param StrictDispatcher              $eventDispatcher
     * @param PlatformConfigurationHandler  $config
     * @param MailManager                   $mailManager
     * @param UserManager                   $manager
     * @param RegistrationManager           $registrationManager
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authChecker,
        StrictDispatcher $eventDispatcher,
        PlatformConfigurationHandler $config,
        UserManager $manager,
        MailManager $mailManager,
        RegistrationManager $registrationManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authChecker = $authChecker;
        $this->eventDispatcher = $eventDispatcher;
        $this->config = $config;
        $this->manager = $manager;
        $this->mailManager = $mailManager;
        $this->registrationManager = $registrationManager;
    }

    public function getName()
    {
        return 'user';
    }

    public function getClass()
    {
        return User::class;
    }

    /**
     * @ApiDoc(
     *     description="Finds an object class $class.",
     *     parameters={
     *          {"name": "id", "type": {"string", "integer"}, "description": "The object id or uuid or publicUrl"}
     *     },
     *     response={"$object"}
     * )
     *
     * @param Request    $request
     * @param string|int $id
     * @param string     $class
     *
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, $class)
    {
        $object = $this->find($class, $id);

        if (!$object) {
            $object = $this->om->getRepository($class)->findOneBy(['publicUrl' => $id]);
        }

        return $object ?
            new JsonResponse(
                $this->serializer->serialize($object, [Options::SERIALIZE_FACET])
            ) :
            new JsonResponse("No object found for id {$id} of class {$class}", 404);
    }

    /**
     * @ApiDoc(
     *     description="List the objects of class $class.",
     *     queryString={
     *         "$finder",
     *         {"name": "page", "type": "integer", "description": "The queried page."},
     *         {"name": "limit", "type": "integer", "description": "The max amount of objects per page."},
     *         {"name": "sortBy", "type": "string", "description": "Sort by the property if you want to."}
     *     },
     *     response={"$list"}
     * )
     *
     * @param Request $request
     * @param string  $class
     *
     * @return JsonResponse
     */
    public function listAction(Request $request, $class)
    {
        if (!$this->authChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedException();
        }

        return parent::listAction($request, $class);
    }

    /**
     * @ApiDoc(
     *     description="Create the personal workspaces of an array of users.",
     *     queryString={
     *         {"name": "ids[]", "type": {"string", "integer"}, "description": "The object id or uuid."}
     *     }
     * )
     * @EXT\Route("/pws", name="apiv2_users_pws_create")
     * @EXT\Method("POST")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function createPersonalWorkspaceAction(Request $request)
    {
        /** @var User[] $users */
        $users = $this->decodeIdsString($request, User::class);

        $this->om->startFlushSuite();

        foreach ($users as $user) {
            if (!$user->getPersonalWorkspace()) {
                $this->manager->setPersonalWorkspace($user);
            }
        }
        $this->om->endFlushSuite();

        return new JsonResponse(array_map(function (User $user) {
            return $this->serializer->serialize($user);
        }, $users));
    }

    /**
     * @ApiDoc(
     *     description="Remove the personal workspaces of an array of users.",
     *     queryString={
     *         {"name": "ids[]", "type": {"string", "integer"}, "description": "The object id or uuid."}
     *     }
     * )
     * @EXT\Route("/pws", name="apiv2_users_pws_delete")
     * @EXT\Method("DELETE")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function deletePersonalWorkspaceAction(Request $request)
    {
        /** @var User[] $users */
        $users = $this->decodeIdsString($request, User::class);

        $this->om->startFlushSuite();

        foreach ($users as $user) {
            $personalWorkspace = $user->getPersonalWorkspace();

            if ($personalWorkspace) {
                $this->crud->delete($personalWorkspace);
            }
        }
        $this->om->endFlushSuite();

        return new JsonResponse(array_map(function (User $user) {
            return $this->serializer->serialize($user);
        }, $users));
    }

    /**
     * @ApiDoc(
     *     description="Create and log a user.",
     *     body={
     *         "schema":"$schema"
     *     }
     * )
     * @EXT\Route("/register", name="apiv2_user_register")
     * @EXT\Method("POST")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function registerAction(Request $request)
    {
        $data = $this->decodeRequest($request);

        $organizationRepository = $this->om->getRepository(Organization::class);

        $organization = null;
        $autoOrganization = $this->config->getParameter('registration.force_organization_creation');
        // step one: creation the organization if it's here. If it exists, we fetch it.
        if ($autoOrganization) {
            // try to find orga first
            // first find by vat
            if (isset($data['mainOrganization'])) {
                if (isset($data['mainOrganization']['vat']) && null !== $data['mainOrganization']['vat']) {
                    $organization = $organizationRepository
                      ->findOneBy(['vat' => $data['mainOrganization']['vat']]);
                // then by code
                } else {
                    $organization = $organizationRepository
                      ->findOneBy(['code' => $data['mainOrganization']['code']]);
                }
            }

            if (!$organization && isset($data['mainOrganization'])) {
                $organization = $this->crud->create(Organization::class, $data['mainOrganization']);
            }

            // error handling
            if (is_array($organization)) {
                return new JsonResponse($organization, 422);
            }
        }

        /** @var array|User $user */
        $user = $this->crud->create(
            User::class,
            $this->decodeRequest($request),
            array_merge($this->options['create'], [Options::VALIDATE_FACET])
        );

        // error handling
        if (is_array($user)) {
            return new JsonResponse($user, 422);
        }

        if ($organization) {
            $this->crud->replace($user, 'mainOrganization', $organization);
        }

        // TODO : dispatch an event for user registration and do next in a listener in AuthenticationBundle
        $selfLog = $this->config->getParameter('registration.auto_logging');
        $validation = $this->config->getParameter('registration.validation');
        // auto log user if option is set and account doesn't need to be validated
        if ($selfLog && PlatformDefaults::REGISTRATION_MAIL_VALIDATION_FULL !== $validation && 'anon.' === $this->tokenStorage->getToken()->getUser()) {
            return $this->registrationManager->loginUser($user, $request);
        }

        return new JsonResponse(null, 204);
    }

    /**
     * @ApiDoc(
     *     description="Get the list of user in that share the current user organizations (and sub organizations).",
     *     queryString={
     *         "$finder=Claroline\CoreBundle\Entity\User&!recursiveOrXOrganization",
     *         {"name": "page", "type": "integer", "description": "The queried page."},
     *         {"name": "limit", "type": "integer", "description": "The max amount of objects per page."},
     *         {"name": "sortBy", "type": "string", "description": "Sort by the property if you want to."}
     *     }
     * )
     * @EXT\Route("/list/registerable", name="apiv2_user_list_registerable")
     * @EXT\Method({"GET", "POST"})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param User    $user
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listRegisterableAction(User $user, Request $request)
    {
        $filters = $this->authChecker->isGranted('ROLE_ADMIN') ?
          [] :
          ['recursiveOrXOrganization' => array_map(function (Organization $organization) {
              return $organization->getUuid();
          }, $user->getOrganizations())];

        //here we look to the posted search data

        $data = json_decode($request->getContent(), true);

        if (isset($data['textSearch'])) {
            $text = $data['textSearch'];
            $data = array_map(function ($data) {
                //trim and do other stuff here
                return $data;
            }, str_getcsv($text, PHP_EOL));
            $filters['globalSearch'] = $data;
        }

        return new JsonResponse($this->finder->search(
            User::class,
            array_merge($request->query->all(), ['hiddenFilters' => $filters])
        ));
    }

    /**
     * @ApiDoc(
     *     description="Get the list of user in that share the current user managed organizations (and sub organizations).",
     *     queryString={
     *         "$finder=Claroline\CoreBundle\Entity\User&!recursiveOrXOrganization",
     *         {"name": "page", "type": "integer", "description": "The queried page."},
     *         {"name": "limit", "type": "integer", "description": "The max amount of objects per page."},
     *         {"name": "sortBy", "type": "string", "description": "Sort by the property if you want to."}
     *     }
     * )
     * @EXT\Route("/list/managed/organization", name="apiv2_user_list_managed_organization")
     * @EXT\Method("GET")
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param User    $user
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listManagedOrganizationAction(User $user, Request $request)
    {
        $filters = $this->authChecker->isGranted('ROLE_ADMIN') ?
          [] :
          [
            'recursiveOrXOrganization' => array_map(function (Organization $organization) {
                return $organization->getUuid();
            }, $user->getAdministratedOrganizations()->toArray()),
          ];

        return new JsonResponse($this->finder->search(
            User::class,
            array_merge($request->query->all(), ['hiddenFilters' => $filters])
        ));
    }

    /**
     * @EXT\Route("/{keep}/{remove}/merge", name="apiv2_user_merge")
     * @EXT\Method("PUT")
     * @EXT\ParamConverter("keep", options={"mapping": {"keep": "uuid"}})
     * @EXT\ParamConverter("remove", options={"mapping": {"remove": "uuid"}})
     *
     * @param User $keep
     * @param User $remove
     *
     * @return JsonResponse
     */
    public function mergeUsersAction(User $keep, User $remove)
    {
        // Dispatching an event for letting plugins and core do what they need to do
        /** @var MergeUsersEvent $event */
        $event = $this->eventDispatcher->dispatch(
            'merge_users',
            'User\MergeUsers',
            [
                $keep,
                $remove,
            ]
        );

        $keep_username = $keep->getUsername();
        $remove_username = $remove->getUsername();

        // Delete old user
        $this->crud->deleteBulk([$remove], [Options::SOFT_DELETE]);

        $event->addMessage("[CoreBundle] user removed: $remove_username");
        $event->addMessage("[CoreBundle] user kept: $keep_username");

        return new JsonResponse($event->getMessages());
    }

    /**
     * @ApiDoc(
     *     description="Enable a list of users.",
     *     queryString={
     *         {"name": "ids[]", "type": {"string", "integer"}, "description": "The object id or uuid."}
     *     }
     * )
     * @EXT\Route("/enable", name="apiv2_users_enable")
     * @EXT\Method("PUT")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function enableUsersAction(Request $request)
    {
        /** @var User[] $users */
        $users = $this->decodeIdsString($request, User::class);

        $this->om->startFlushSuite();

        foreach ($users as $user) {
            $user->setIsEnabled(true);
            $this->om->persist($user);
        }
        $this->om->endFlushSuite();

        return new JsonResponse(array_map(function (User $user) {
            return $this->serializer->serialize($user);
        }, $users));
    }

    /**
     * @ApiDoc(
     *     description="Disable a list of users.",
     *     queryString={
     *         {"name": "ids[]", "type": {"string", "integer"}, "description": "The object id or uuid."}
     *     }
     * )
     * @EXT\Route("/disable", name="apiv2_users_disable")
     * @EXT\Method("PUT")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function disableUsersAction(Request $request)
    {
        /** @var User[] $users */
        $users = $this->decodeIdsString($request, User::class);

        $this->om->startFlushSuite();

        foreach ($users as $user) {
            $user->setIsEnabled(false);
            $this->om->persist($user);
        }
        $this->om->endFlushSuite();

        return new JsonResponse(array_map(function (User $user) {
            return $this->serializer->serialize($user);
        }, $users));
    }

    /**
     * @ApiDoc(
     *     description="Reset a list of user password.",
     *     queryString={
     *         {"name": "ids[]", "type": {"string", "integer"}, "description": "The object id or uuid."}
     *     }
     * )
     * @EXT\Route("/password/reset", name="apiv2_users_password_reset")
     * @EXT\Method("PUT")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function resetPasswordAction(Request $request)
    {
        /** @var User[] $users */
        $users = $this->decodeIdsString($request, User::class);

        $this->om->startFlushSuite();

        foreach ($users as $user) {
            $user->setHashTime(time());
            $password = sha1(rand(1000, 10000).$user->getUsername().$user->getSalt());
            $user->setResetPasswordHash($password);
            $this->om->persist($user);
            $this->mailManager->sendForgotPassword($user);
        }
        $this->om->endFlushSuite();

        return new JsonResponse(array_map(function (User $user) {
            return $this->serializer->serialize($user);
        }, $users));
    }

    public function getOptions()
    {
        return array_merge(parent::getOptions(), [
            'deleteBulk' => [Options::SOFT_DELETE],
            'create' => [
                //maybe move these options in an other class
                Options::ADD_NOTIFICATIONS,
                Options::WORKSPACE_VALIDATE_ROLES,
            ],
            'get' => [Options::SERIALIZE_FACET],
            'update' => [Options::SERIALIZE_FACET],
        ]);
    }

    /**
     * @return array
     */
    public function getRequirements()
    {
        return array_merge(parent::getRequirements(), [
          'get' => ['id' => '^(?!.*(schema|copy|parameters|find|doc|csv|current|\/)).*'],
          'update' => ['id' => '^(?!.*(schema|parameters|find|doc|csv|current|\/)).*'],
          'exist' => [],
        ]);
    }
}
