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
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/user")
 */
class UserController extends AbstractCrudController
{
    /** @var StrictDispatcher */
    private $eventDispatcher;

    /** @var MailManager */
    private $mailManager;

    /**
     * UserController constructor.
     *
     * @DI\InjectParams({
     *    "eventDispatcher" = @DI\Inject("claroline.event.event_dispatcher"),
     *    "mailManager"     = @DI\Inject("claroline.manager.mail_manager")
     * })
     *
     * @param StrictDispatcher $eventDispatcher
     * @param MailManager      $mailManager
     */
    public function __construct(StrictDispatcher $eventDispatcher, MailManager $mailManager)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->mailManager = $mailManager;
    }

    public function getName()
    {
        return 'user';
    }

    use HasRolesTrait;
    use HasOrganizationsTrait;
    use HasGroupsTrait;

    /**
     * @Route("/pws/create", name="apiv2_users_pws_create")
     * @Method("POST")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function createPersonalWorkspaceAction(Request $request)
    {
        $users = $this->decodeIdsString($request, 'Claroline\CoreBundle\Entity\User');

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
     * @Route("/pws/delete", name="apiv2_users_pws_delete")
     * @Method("DELETE")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function deletePersonalWorkspaceAction(Request $request)
    {
        $users = $this->decodeIdsString($request, 'Claroline\CoreBundle\Entity\User');

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
     * @Route("/user/login", name="apiv2_user_create_and_login")
     * @Method("POST")
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

        if ($selfLog && 'anon.' === $this->container->get('security.token_storage')->getToken()->getUser()) {
            $this->options['create'][] = Options::USER_SELF_LOG;
        }

        $organization = null;

        if ($autoOrganization) {
            //try to find orga first
            //first find by vat
            if (isset($data['mainOrganization'])) {
                if (isset($data['mainOrganization']['vat']) && $data['mainOrganization']['vat'] !== null) {
                    $organization = $organizationRepository
                      ->findOneByVat($data['mainOrganization']['vat']);
                //then by code
                } else {
                    $organization = $organizationRepository
                      ->findOneByCode($data['mainOrganization']['code']);
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
           'Claroline\CoreBundle\Entity\User',
            $this->decodeRequest($request)
        );

        //error handling
        if (is_array($user)) {
            return new JsonResponse($user, 400);
        }

        if ($organization) {
            $this->crud->replace($user, 'mainOrganization', $organization);
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
        ];

        return [
            'deleteBulk' => [Options::SOFT_DELETE],
            'create' => $create,
            'get' => [Options::SERIALIZE_FACET],
        ];
    }

    /**
     * @Route(
     *    "/currentworkspaces",
     *    name="apiv2_user_currentworkspace"
     * )
     * @ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     * @Method("GET")
     *
     * @param User $user
     *
     * @return JsonResponse
     */
    public function getCurrentWorkspacesAction(User $user)
    {
        return new JsonResponse($this->finder->search(
            'Claroline\CoreBundle\Entity\Workspace\Workspace',
            ['filters' => ['user' => $user->getUuid()]],
            $this->options['list']
        ));
    }

    /**
     * @Route(
     *    "/list/registerable",
     *    name="apiv2_user_list_registerable"
     * )
     * @Method("GET")
     * @ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param Workspace $workspace
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

        return new JsonResponse($this->finder->search(
            'Claroline\CoreBundle\Entity\User',
            array_merge($request->query->all(), ['hiddenFilters' => $filters])
        ));
    }

    /**
     * @Route(
     *    "/list/managed/organization",
     *    name="apiv2_user_list_managed_organization"
     * )
     * @Method("GET")
     * @ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param Workspace $workspace
     *
     * @return JsonResponse
     */
    public function listManagedOrganizationAction(User $user, Request $request)
    {
        $filters = $this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN') ?
          [] :
          ['workspace' => array_map(function (Organization $organization) {
              return $organization->getUuid();
          }, $user->getAdministratedOrganizations()->toArray())];

        return new JsonResponse($this->finder->search(
            'Claroline\CoreBundle\Entity\User',
            array_merge($request->query->all(), ['hiddenFilters' => $filters])
        ));
    }

    public function getClass()
    {
        return "Claroline\CoreBundle\Entity\User";
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
     * @Route(
     *    "/list/managed/workspace",
     *    name="apiv2_user_list_managed_workspace"
     * )
     * @Method("GET")
     * @ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param Workspace $workspace
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
            'Claroline\CoreBundle\Entity\User',
            array_merge($request->query->all(), ['hiddenFilters' => $filters])
        ));
    }

    /**
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
        $users = $this->decodeIdsString($request, 'Claroline\CoreBundle\Entity\User');

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
        $users = $this->decodeIdsString($request, 'Claroline\CoreBundle\Entity\User');

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
        $users = $this->decodeIdsString($request, 'Claroline\CoreBundle\Entity\User');

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
}
