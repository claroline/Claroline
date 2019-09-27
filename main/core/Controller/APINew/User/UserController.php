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
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\User\MergeUsersEvent;
use Claroline\CoreBundle\Manager\MailManager;
use Claroline\CoreBundle\Manager\UserManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/user")
 */
class UserController extends AbstractCrudController
{
    use HasRolesTrait;
    use HasOrganizationsTrait;
    use HasGroupsTrait;

    /** @var AuthorizationCheckerInterface */
    private $authChecker;

    /** @var StrictDispatcher */
    private $eventDispatcher;

    /** @var UserManager */
    private $manager;

    /** @var MailManager */
    private $mailManager;

    /**
     * UserController constructor.
     *
     * @param AuthorizationCheckerInterface $authChecker
     * @param StrictDispatcher              $eventDispatcher
     * @param MailManager                   $mailManager
     * @param UserManager                   $manager
     */
    public function __construct(
        AuthorizationCheckerInterface $authChecker,
        StrictDispatcher $eventDispatcher,
        UserManager $manager,
        MailManager $mailManager
    ) {
        $this->authChecker = $authChecker;
        $this->eventDispatcher = $eventDispatcher;
        $this->manager = $manager;
        $this->mailManager = $mailManager;
    }

    public function getName()
    {
        return 'user';
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
            throw new AccessDeniedException('You need to authenticate first');
        }

        return parent::listAction($request, $class);
    }

    /**
     * @ApiDoc(
     *     description="List the objects of class $class.",
     *     response={"$object"}
     * )
     * @Route("/current", name="apiv2_users_current")
     * @Method("GET")
     *
     * @param Request $request
     * @param string  $class
     *
     * @return JsonResponse
     */
    public function currentAction(Request $request)
    {
        $user = $this->container->get('security.token_storage')->getToken()->getUser();

        if ('anon.' === $user) {
            throw new \Exception('No user authentified');
        }

        return new JsonResponse($this->serializer->serialize($user));
    }

    /**
     * @ApiDoc(
     *     description="Create the personal workspaces of an array of users.",
     *     queryString={
     *         {"name": "ids[]", "type": {"string", "integer"}, "description": "The object id or uuid."}
     *     }
     * )
     * @Route("/pws/create", name="apiv2_users_pws_create")
     * @Method("POST")
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
                $this->container->get('claroline.manager.user_manager')->setPersonalWorkspace($user);
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
     * @Route("/pws/delete", name="apiv2_users_pws_delete")
     * @Method("DELETE")
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
                $this->container->get('claroline.manager.workspace_manager')->deleteWorkspace($personalWorkspace);
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
     * @Route("/user/login", name="apiv2_user_create_and_login")
     * @Method("POST")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function createAndLoginAction(Request $request)
    {
        //there is a little bit of computation involved here (ie, do we need to validate the account or stuff like this)
        //but keep it easy for now because an other route could be relevant
        $selfLog = true;
        $autoOrganization = $this->container
            ->get('claroline.config.platform_config_handler')
            ->getParameter('force_organization_creation');

        $organizationRepository = $this->container->get('claroline.persistence.object_manager')
            ->getRepository('ClarolineCoreBundle:Organization\Organization');

        //step one: creation the organization if it's here. If it exists, we fetch it.
        $data = $this->decodeRequest($request);

        $organization = null;

        if ($autoOrganization) {
            //try to find orga first
            //first find by vat
            if (isset($data['mainOrganization'])) {
                if (isset($data['mainOrganization']['vat']) && null !== $data['mainOrganization']['vat']) {
                    $organization = $organizationRepository
                      ->findOneBy(['vat' => $data['mainOrganization']['vat']]);
                //then by code
                } else {
                    $organization = $organizationRepository
                      ->findOneBy(['code' => $data['mainOrganization']['code']]);
                }
            }

            if (!$organization && isset($data['mainOrganization'])) {
                $organization = $this->crud->create(
                    'Claroline\CoreBundle\Entity\Organization\Organization',
                    $data['mainOrganization']
                );
            }

            //error handling
            if (is_array($organization)) {
                return new JsonResponse($organization, 400);
            }
        }

        $user = $this->crud->create(
            User::class,
            $this->decodeRequest($request),
            array_merge($this->options['create'], [Options::VALIDATE_FACET])
        );

        //error handling
        if (is_array($user)) {
            return new JsonResponse($user, 400);
        }

        if ($organization) {
            $this->crud->replace($user, 'mainOrganization', $organization);
        }

        if ($selfLog && 'anon.' === $this->container->get('security.token_storage')->getToken()->getUser()) {
            $this->manager->logUser($user);
        }

        return new JsonResponse(
            $this->serializer->serialize($user, $this->options['get']),
            201
        );
    }

    public function getOptions()
    {
        $create = [
            //maybe move these options in an other class
            Options::SEND_EMAIL,
            Options::ADD_NOTIFICATIONS,
            Options::WORKSPACE_VALIDATE_ROLES,
        ];

        return [
            'deleteBulk' => [Options::SOFT_DELETE],
            'create' => $create,
            'get' => [Options::SERIALIZE_FACET],
            'update' => [Options::SERIALIZE_FACET],
        ];
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
     * @Route(
     *    "/list/registerable",
     *    name="apiv2_user_list_registerable"
     * )
     * @Method({"GET", "POST"})
     * @ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param User    $user
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listRegisterableAction(User $user, Request $request)
    {
        $filters = $this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN') ?
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
     * @Route(
     *    "/list/managed/organization",
     *    name="apiv2_user_list_managed_organization"
     * )
     * @Method("GET")
     * @ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param User    $user
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listManagedOrganizationAction(User $user, Request $request)
    {
        $filters = $this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN') ?
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

    public function getClass()
    {
        return User::class;
    }

    /**
     * @Route(
     *    "/{keep}/{remove}/merge",
     *    name="apiv2_user_merge"
     * )
     * @Method("PUT")
     * @ParamConverter("keep", options={"mapping": {"keep": "uuid"}})
     * @ParamConverter("remove", options={"mapping": {"remove": "uuid"}})
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
     *     description="Get the list of managed workspaces for the current user.",
     *     queryString={
     *         "$finder=Claroline\CoreBundle\Entity\Workspace\Workspace&!isManager",
     *         {"name": "page", "type": "integer", "description": "The queried page."},
     *         {"name": "limit", "type": "integer", "description": "The max amount of objects per page."},
     *         {"name": "sortBy", "type": "string", "description": "Sort by the property if you want to."}
     *     }
     * )
     * @Route(
     *    "/list/managed/workspace",
     *    name="apiv2_user_list_managed_workspace"
     * )
     * @Method("GET")
     * @ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param User    $user
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @todo move in workspace api
     */
    public function listManagedWorkspaceAction(User $user, Request $request)
    {
        $managedWorkspaces = $this->finder->fetch(
            'Claroline\CoreBundle\Entity\Workspace\Workspace',
            ['user' => $user->getId(), 'isManager' => true]
        );

        $filters = $this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN') ?
          [] :
          ['workspace' => array_map(function (Workspace $workspace) {
              return $workspace->getUuid();
          }, $managedWorkspaces)];

        return new JsonResponse($this->finder->search(
            User::class,
            array_merge($request->query->all(), ['hiddenFilters' => $filters])
        ));
    }

    /**
     * @ApiDoc(
     *     description="Enable a list of users.",
     *     queryString={
     *         {"name": "ids[]", "type": {"string", "integer"}, "description": "The object id or uuid."}
     *     }
     * )
     * @Route(
     *    "/users/enable",
     *    name="apiv2_users_enable"
     * )
     * @Method("PUT")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function usersEnableAction(Request $request)
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
     * @Route(
     *    "/users/disable",
     *    name="apiv2_users_disable"
     * )
     * @Method("PUT")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function usersDisableAction(Request $request)
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
     * @Route(
     *    "/password/reset",
     *    name="apiv2_users_password_reset"
     * )
     * @Method("PUT")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function passwordResetAction(Request $request)
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

    /**
     * @return array
     */
    public function getDefaultRequirements()
    {
        return [
          'get' => ['id' => '^(?!.*(schema|copy|parameters|find|doc|csv|current|\/)).*'],
          'update' => ['id' => '^(?!.*(schema|parameters|find|doc|csv|current|\/)).*'],
          'exist' => [],
        ];
    }
}
