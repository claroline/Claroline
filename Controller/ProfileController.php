<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Form\ProfileType;
use Claroline\CoreBundle\Form\ResetPasswordType;
use Claroline\CoreBundle\Manager\LocaleManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\UserManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("hasRole('ROLE_USER')")
 *
 * Controller of the user profile.
 */
class ProfileController extends Controller
{
    private $userManager;
    private $roleManager;
    private $eventDispatcher;
    private $security;
    private $request;
    private $localeManager;

    /**
     * @DI\InjectParams({
     *     "userManager"        = @DI\Inject("claroline.manager.user_manager"),
     *     "roleManager"        = @DI\Inject("claroline.manager.role_manager"),
     *     "eventDispatcher"    = @DI\Inject("claroline.event.event_dispatcher"),
     *     "security"           = @DI\Inject("security.context"),
     *     "request"            = @DI\Inject("request"),
     *     "localeManager"      = @DI\Inject("claroline.common.locale_manager")
     * })
     */
    public function __construct(
        UserManager $userManager,
        RoleManager $roleManager,
        StrictDispatcher $eventDispatcher,
        SecurityContextInterface $security,
        Request $request,
        LocaleManager $localeManager
    )
    {
        $this->userManager = $userManager;
        $this->roleManager = $roleManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->security = $security;
        $this->request = $request;
        $this->localeManager = $localeManager;
    }

    private function isInRoles($role, $roles)
    {
        foreach ($roles as $current) {
            if ($role->getId() == $current->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @EXT\Route(
     *     "/form/{user}",
     *     name="claro_profile_form"
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Profile:profileForm.html.twig")
     * @EXT\ParamConverter("loggedUser", options={"authenticatedUser" = true})
     * Displays an editable form of the current user's profile.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function formAction(User $user, User $loggedUser)
    {
        $isAdmin = $this->get('security.context')->isGranted('ROLE_ADMIN');

        if ($user !== $loggedUser && !$isAdmin) {
            throw new AccessDeniedException();
        }

        $roles = $this->roleManager->getPlatformRoles($user);
        $form = $this->createForm(
            new ProfileType($roles, $isAdmin, $this->localeManager->getAvailableLocales()), $user
        );

        return array('profile_form' => $form->createView(), 'user' => $user);
    }

    /**
     * @EXT\Route(
     *     "/update/{user}",
     *     name="claro_profile_update"
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Profile:profileForm.html.twig")
     * @EXT\ParamConverter("loggedUser", options={"authenticatedUser" = true})
     * Updates the user's profile and redirects to the profile form.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateAction(User $user, User $loggedUser)
    {
        $isAdmin = $this->get('security.context')->isGranted('ROLE_ADMIN');

        if ($user !== $loggedUser && !$isAdmin) {
            throw new AccessDeniedException();
        }

        $roles = $this->roleManager->getPlatformRoles($loggedUser);
        $form = $this->createForm(
            new ProfileType($roles, $isAdmin, $this->localeManager->getAvailableLocales()), $user
        );
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $user = $form->getData();
            $this->userManager->rename($user, $user->getUsername());
            $em = $this->getDoctrine()->getManager();
            $unitOfWork = $em->getUnitOfWork();
            $unitOfWork->computeChangeSets();
            $changeSet = $unitOfWork->getEntityChangeSet($user);
            $newRoles = $form['platformRoles']->getData();
            $this->userManager->setPlatformRoles($user, $newRoles);

            $rolesChangeSet = array();
            //Detect added
            foreach ($newRoles as $role) {
                if (!$this->isInRoles($role, $roles)) {
                    $rolesChangeSet[$role->getTranslationKey()] = array(false, true);
                }
            }
            //Detect removed
            foreach ($roles as $role) {
                if (!$this->isInRoles($role, $newRoles)) {
                    $rolesChangeSet[$role->getTranslationKey()] = array(true, false);
                }
            }
            if (count($rolesChangeSet) > 0) {
                $changeSet['roles'] = $rolesChangeSet;
            }

            $this->userManager->uploadAvatar($user);
            $this->eventDispatcher->dispatch(
                'log',
                'Log\LogUserUpdate',
                array($user, $changeSet)
            );

            if ($isAdmin) {
                return $this->redirect($this->generateUrl('claro_admin_user_list'));
            }

            return $this->redirect($this->generateUrl('claro_profile_view', array('userId' => $user->getId())));

        }

        return array('profile_form' => $form->createView(), 'user' => $user);
    }

    /**
     * @EXT\Route(
     *     "/view/{userId}",
     *      name="claro_profile_view"
     * )
     * @EXT\ParamConverter(
     *      "user",
     *      class="ClarolineCoreBundle:User",
     *      options={"id" = "userId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Profile:profile.html.twig")
     *
     * Displays the public profile of an user.
     *
     * @param \Claroline\CoreBundle\Entity\User $user
     * @param int                               $page
     */
    public function viewAction(User $user, $page = 1)
    {
        $query   = $this->getDoctrine()->getRepository('ClarolineCoreBundle:Badge\UserBadge')->findByUser($user, false);
        $adapter = new DoctrineORMAdapter($query);
        $pager   = new Pagerfanta($adapter);

        try {
            $pager->setCurrentPage($page);
        } catch (NotValidCurrentPageException $exception) {
            throw new NotFoundHttpException();
        }

        /** @var \Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler $platformConfigHandler */
        $platformConfigHandler = $this->get('claroline.config.platform_config_handler');

        return array(
            'user'     => $user,
            'pager'    => $pager,
            'language' => $platformConfigHandler->getParameter('locale_language')
        );
    }

    /**
     * @EXT\Route(
     *     "/password/form/{user}",
     *      name="claro_password_form"
     * )
     * @EXT\Template("ClarolineCoreBundle:Profile:passwordForm.html.twig")
     *
     * Displays the password reset form for a user.
     *
     * @param \Claroline\CoreBundle\Entity\User $user
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @return array
     */
    public function editPasswordFormAction(User $user)
    {
        $security = $this->get('security.context');

        if ($security->getToken()->getUser() !== $user && !$security->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(new ResetPasswordType(), $user);

        return array('form' => $form->createView(), 'user' => $user);
    }

    /**
     * @EXT\Route(
     *     "/password/edit/{user}",
     *      name="claro_password"
     * )
     * @EXT\Template("ClarolineCoreBundle:Profile:passwordForm.html.twig")
     *
     * Updates the password of a user.
     *
     * @param \Claroline\CoreBundle\Entity\User $user
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function editPasswordAction(User $user)
    {
        $security = $this->get('security.context');

        if ($security->getToken()->getUser() !== $user && !$security->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(new ResetPasswordType(), $user);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $user = $form->getData();
            $em = $this->get('doctrine.orm.entity_manager');
            $em->persist($user);
            $em->flush();

            return $this->redirect($this->generateUrl('claro_profile_view', array('userId' => $user->getId())));
        }

        return array('form' => $form->createView(), 'user' => $user);
    }
}
