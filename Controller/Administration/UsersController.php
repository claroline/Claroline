<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\Administration;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Form\Administration\UserPropertiesType;
use Claroline\CoreBundle\Manager\AuthenticationManager;
use Claroline\CoreBundle\Manager\LocaleManager;
use Claroline\CoreBundle\Manager\MailManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;

class UsersController extends Controller
{
    private $userManager;
    private $roleManager;
    private $eventDispatcher;
    private $formFactory;
    private $request;
    private $mailManager;
    private $workspaceManager;
    private $localeManager;
    private $router;
    private $toolManager;
    private $userAdminTool;
    private $configHandler;

    /**
     * @DI\InjectParams({
     *     "userManager"            = @DI\Inject("claroline.manager.user_manager"),
     *     "workspaceManager"       = @DI\Inject("claroline.manager.workspace_manager"),
     *     "roleManager"            = @DI\Inject("claroline.manager.role_manager"),
     *     "eventDispatcher"        = @DI\Inject("claroline.event.event_dispatcher"),
     *     "formFactory"            = @DI\Inject("claroline.form.factory"),
     *     "request"                = @DI\Inject("request"),
     *     "mailManager"            = @DI\Inject("claroline.manager.mail_manager"),
     *     "localeManager"          = @DI\Inject("claroline.common.locale_manager"),
     *     "router"                 = @DI\Inject("router"),
     *     "sc"                     = @DI\Inject("security.context"),
     *     "toolManager"            = @DI\Inject("claroline.manager.tool_manager"),
     *     "authenticationManager"  = @DI\Inject("claroline.common.authentication_manager"),
     *     "configHandler"          = @DI\Inject("claroline.config.platform_config_handler")
     * })
     */
    public function __construct(
        UserManager $userManager,
        RoleManager $roleManager,
        WorkspaceManager $workspaceManager,
        StrictDispatcher $eventDispatcher,
        FormFactory $formFactory,
        Request $request,
        MailManager $mailManager,
        LocaleManager $localeManager,
        RouterInterface $router,
        SecurityContextInterface $sc,
        ToolManager $toolManager,
        AuthenticationManager $authenticationManager,
        PlatformConfigurationHandler $configHandler
    )
    {
        $this->userManager           = $userManager;
        $this->roleManager           = $roleManager;
        $this->eventDispatcher       = $eventDispatcher;
        $this->formFactory           = $formFactory;
        $this->request               = $request;
        $this->mailManager           = $mailManager;
        $this->localeManager         = $localeManager;
        $this->router                = $router;
        $this->workspaceManager      = $workspaceManager;
        $this->sc                    = $sc;
        $this->toolManager           = $toolManager;
        $this->userAdminTool         = $this->toolManager->getAdminToolByName('user_management');
        $this->authenticationManager = $authenticationManager;
        $this->configHandler         = $configHandler;
    }

    /**
     * @EXT\Route("/menu", name="claro_admin_users_management")
     * @EXT\Template
     *
     * @return Response
     */
    public function indexAction()
    {
        $this->checkOpen();
        $canUserBeCreated = $this->roleManager->validateRoleInsert(
            new User(),
            $this->roleManager->getRoleByName('ROLE_USER')
        );

        return array('canUserBeCreated' => $canUserBeCreated);
    }

    /**
     * @EXT\Route("/new", name="claro_admin_user_creation_form")
     * @EXT\Template
     *
     * Displays the user creation form.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function userCreationFormAction()
    {
        $this->checkOpen();
        $roleUser = $this->roleManager->getRoleByName('ROLE_USER');
        $isAdmin = ($this->sc->isGranted('ROLE_ADMIN')) ? true : false;
        $roles = $this->roleManager->getAllPlatformRoles();
        $unavailableRoles = [];

        foreach ($roles as $role) {
            $isAvailable = $this->roleManager->validateRoleInsert(new User(), $role);

            if (!$isAvailable) {
                $unavailableRoles[] = $role;
            }
        }

        $form = $this->formFactory->create(
            FormFactory::TYPE_USER_FULL,
            array(
                array($roleUser),
                $this->localeManager->getAvailableLocales(),
                $isAdmin,
                $this->authenticationManager->getDrivers()
            )
        );

        $error = null;

        if (!$this->mailManager->isMailerAvailable()) {
            $error = 'mail_not_available';
        }

        return array(
            'form_complete_user' => $form->createView(),
            'error' => $error,
            'unavailableRoles' => $unavailableRoles
        );
    }

    /**
     * @EXT\Route("/new/submit", name="claro_admin_create_user")
     * @EXT\Method("POST")
     * @EXT\ParamConverter("currentUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCoreBundle:Administration/Users:userCreationForm.html.twig")
     *
     * Creates an user (and its personal workspace) and redirects to the user list.
     *
     * @param User $currentUser
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function createAction(User $currentUser)
    {
        $this->checkOpen();
        $translator = $this->get('translator');
        $sessionFlashBag = $this->get('session')->getFlashBag();
        $roleUser = $this->roleManager->getRoleByName('ROLE_USER');
        $isAdmin = ($this->sc->isGranted('ROLE_ADMIN')) ? true : false;

        $form = $this->formFactory->create(
            FormFactory::TYPE_USER_FULL,
            array(
                array($roleUser),
                $this->localeManager->getAvailableLocales(),
                $isAdmin,
                $this->authenticationManager->getDrivers()
            )
        );
        $form->handleRequest($this->request);

        $unavailableRoles = [];

        foreach ($form->get('platformRoles')->getData() as $role) {
            $isAvailable = $this->roleManager->validateRoleInsert(new User(), $role);

            if (!$isAvailable) {
                $unavailableRoles[] = $role;
            }
        }

        $isAvailable = $this->roleManager->validateRoleInsert(new User(), $roleUser);

        if (!$isAvailable) {
            $unavailableRoles[] = $roleUser;
        }

        $unavailableRoles = array_unique($unavailableRoles);

        if ($form->isValid() && count($unavailableRoles) === 0) {
            $user = $form->getData();
            $newRoles = $form->get('platformRoles')->getData();
            $this->userManager->insertUserWithRoles($user, $newRoles);

            $sessionFlashBag->add('success', $translator->trans('user_creation_success', array(), 'platform'));

            return $this->redirect($this->generateUrl('claro_admin_user_list'));
        }

        $error = null;

        if (!$this->mailManager->isMailerAvailable()) {
            $error = 'mail_not_available';
        }

        return array(
            'form_complete_user' => $form->createView(),
            'error' => $error,
            'unavailableRoles' => $unavailableRoles,
        );
    }

    /**
     * @EXT\Route(
     *     "/",
     *     name="claro_admin_multidelete_user",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("DELETE")
     * @EXT\ParamConverter(
     *     "users",
     *      class="ClarolineCoreBundle:User",
     *      options={"multipleIds" = true}
     * )
     *
     * Removes many users from the platform.
     *
     * @param User[] $users
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(array $users)
    {
        $this->checkOpen();

        foreach ($users as $user) {
            if (!$this->sc->isGranted('ROLE_ADMIN') && $user->hasRole('ROLE_ADMIN')) {
                throw new AccessDeniedException();
            }

            $this->userManager->deleteUser($user);
            $this->eventDispatcher->dispatch('log', 'Log\LogUserDelete', array($user));
        }

        return new Response('user(s) removed', 204);
    }

    /**
     * @EXT\Route(
     *     "/page/{page}/max/{max}/order/{order}/direction/{direction}",
     *     name="claro_admin_user_list",
     *     defaults={"page"=1, "search"="", "max"=50, "order"="id","direction"="ASC"},
     *     options = {"expose"=true}
     * )
     * @EXT\Route(
     *     "/users/page/{page}/search/{search}/max/{max}/order/{order}/direction/{direction}",
     *     name="claro_admin_user_list_search",
     *     defaults={"page"=1, "max"=50, "order"="id","direction"="ASC"},
     *     options = {"expose"=true}
     * )
     * @EXT\Template
     * @EXT\ParamConverter(
     *     "order",
     *     class="Claroline\CoreBundle\Entity\User",
     *     options={"orderable"=true}
     * )
     *
     * Displays the platform user list.
     *
     * @param integer $page
     * @param string  $search
     * @param integer $max
     * @param string  $order
     * @param string  $direction
     *
     * @return array
     */
    public function listAction($page, $search, $max, $order, $direction)
    {
        $this->checkOpen();
        $canUserBeCreated = $this->roleManager->validateRoleInsert(new User(),$this->roleManager->getRoleByName('ROLE_USER'));
        $pager = $search === '' ?
            $this->userManager->getAllUsers($page, $max, $order, $direction) :
            $this->userManager->getUsersByName($search, $page, $max, $order, $direction);

        return array(
            'canUserBeCreated' => $canUserBeCreated,
            'pager' => $pager,
            'search' => $search,
            'max' => $max,
            'order' => $order,
            'direction' => $direction,
        );
    }

    /**
     * @EXT\Route(
     *     "/page/{page}/pic",
     *     name="claro_admin_user_list_pics",
     *     defaults={"page"=1, "search"=""},
     *     options = {"expose"=true}
     * )
     * @EXT\Route(
     *     "/page/{page}/pic/search/{search}",
     *     name="claro_admin_user_list_search_pics",
     *     defaults={"page"=1},
     *     options = {"expose"=true}
     * )
     * @EXT\Template
     *
     * Displays the platform user list.
     *
     * @param integer $page
     * @param string  $search
     *
     * @return array
     */
    public function listPicsAction($page, $search)
    {
        $this->checkOpen();
        $pager = $search === '' ?
            $this->userManager->getAllUsers($page) :
            $this->userManager->getUsersByName($search, $page);

        return array('pager' => $pager, 'search' => $search);
    }

    /**
     * @EXT\Route("/import", name="claro_admin_import_users_form")
     * @EXT\Template
     *
     * @return Response
     */
    public function importFormAction()
    {
        $this->checkOpen();
        $form = $this->formFactory->create(FormFactory::TYPE_USER_IMPORT, array('showRoles' => true));

        return array('form' => $form->createView(), 'error' => null);
    }

    /**
     * @EXT\Route(
     *     "/user/{user}/workspaces/page/{page}/max/{max}",
     *     name="claro_admin_user_workspaces",
     *     defaults={"page"=1, "max"=50},
     *     options={"expose"=true}
     * )
     * @EXT\Template
     *
     * @param User    $user
     * @param integer $page
     * @param integer $max
     *
     * @return array
     */
    public function userWorkspaceListAction(User $user, $page, $max)
    {
        $this->checkOpen();
        $pager = $this->workspaceManager->getOpenableWorkspacesByRolesPager($user->getRoles(), $page, $max);

        return array('user' => $user, 'pager' => $pager, 'page' => $page, 'max' => $max);
    }

    /**
     * @EXT\Route("/import/submit", name="claro_admin_import_users")
     * @EXT\Method("POST")
     * @EXT\Template("ClarolineCoreBundle:Administration/Users:importForm.html.twig")
     *
     * @return Response
     */
    public function importAction()
    {
        $this->checkOpen();
        $form = $this->formFactory->create(FormFactory::TYPE_USER_IMPORT, array('showRoles' => true));
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $file = $form->get('file')->getData();
            $sendMail = $form->get('sendMail')->getData();
            $lines = str_getcsv(file_get_contents($file), PHP_EOL);

            foreach ($lines as $line) {
                $users[] = str_getcsv($line, ';');
            }

            $roleUser = $this->roleManager->getRoleByName('ROLE_USER');
            $max = $roleUser->getMaxUsers();
            $total = $this->userManager->countUsersByRoleIncludingGroup($roleUser);

            if ($total + count($users) > $max) {
                return array('form' => $form->createView(), 'error' => 'role_user unavailable');
            }

            $additionalRole = $form->get('role')->getData();

            if ($additionalRole !== null) {
                $max = $additionalRole->getMaxUsers();
                $total = $this->userManager->countUsersByRoleIncludingGroup($additionalRole);

                if ($total + count($users) > $max) {
                    return array('form' => $form->createView(), 'error' => $additionalRole->getName() . ' unavailable');
                }
            }

            $this->userManager->importUsers($users, $sendMail, null, array($additionalRole));

            return new RedirectResponse($this->router->generate('claro_admin_user_list'));
        }

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route("/export/users/{format}", name="claro_admin_export_users")
     *
     * @return Response
     */
    public function export($format)
    {
        $this->checkOpen();
        $exporter = $this->container->get('claroline.exporter.' . $format);
        $exporterManager = $this->container->get('claroline.manager.exporter_manager');
        $file = $exporterManager->export('Claroline\CoreBundle\Entity\User', $exporter);
        $response = new StreamedResponse();

        $response->setCallBack(
            function () use ($file) {
                readfile($file);
            }
        );

        $response->headers->set('Content-Transfer-Encoding', 'octet-stream');
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename=users.' . $format);

        switch ($format) {
            case 'csv': $response->headers->set('Content-Type', 'text/csv'); break;
            case 'xls': $response->headers->set('Content-Type', 'application/vnd.ms-excel'); break;
        }

        $response->headers->set('Connection', 'close');

        return $response;
    }

    /**
     * @EXT\Route("/user/form/properties", name="claro_admin_user_form_properties")
     *
     * @EXT\Template("ClarolineCoreBundle:Administration/Users:userFormProperties.html.twig")
     */
    public function userFormPropertiesAction()
    {
        $this->checkOpen();
        $platformConfig = $this->configHandler->getPlatformConfig();
        $form = $this->createForm(new UserPropertiesType(), $platformConfig, array(
            'action' => $this->generateUrl('claro_admin_user_form_properties_submit')
        ));

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route("/user/form/properties/submit", name="claro_admin_user_form_properties_submit"),
     *
     * @EXT\Template("ClarolineCoreBundle:Administration/Users:userFormProperties.html.twig")
     */
    public function submitUserFormPropertiesAction()
    {
        $this->checkOpen();
        $platformConfig = $this->configHandler->getPlatformConfig();

        $form = $this->createForm(
            new UserPropertiesType(),
            $platformConfig
        );

        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->configHandler->setParameters(
                array(
                    'createPersonnalWorkspace' => $form['createPersonnalWorkspace']->getData()
                )
            );

            return new RedirectResponse($this->router->generate('claro_admin_users_management'));
        }

        return array('form' => $form->createView());
    }

    private function checkOpen()
    {
        if ($this->sc->isGranted('OPEN', $this->userAdminTool)) {
            return true;
        }

        throw new AccessDeniedException();
    }
}
