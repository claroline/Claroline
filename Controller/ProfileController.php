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
use Claroline\CoreBundle\Entity\UserPublicProfilePreferences;
use Claroline\CoreBundle\Form\UserPublicProfilePreferencesType;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Form\ProfileType;
use Claroline\CoreBundle\Form\ResetPasswordType;
use Claroline\CoreBundle\Form\UserPublicProfileUrlType;
use Claroline\CoreBundle\Manager\LocaleManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\UserManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
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
     *     "userManager"     = @DI\Inject("claroline.manager.user_manager"),
     *     "roleManager"     = @DI\Inject("claroline.manager.role_manager"),
     *     "eventDispatcher" = @DI\Inject("claroline.event.event_dispatcher"),
     *     "security"        = @DI\Inject("security.context"),
     *     "request"         = @DI\Inject("request"),
     *     "localeManager"   = @DI\Inject("claroline.common.locale_manager")
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
     *     "/",
     *      name="claro_profile_view"
     * )
     * @SEC\Secure(roles="ROLE_USER")
     * @EXT\Template()
     * @EXT\ParamConverter("loggedUser", options={"authenticatedUser" = true})
     */
    public function viewAction(User $loggedUser)
    {
        return array(
            'user'  => $loggedUser
        );
    }

    /**
     * @EXT\Route(
     *     "/preferences",
     *      name="claro_user_public_profile_preferences"
     * )
     * @SEC\Secure(roles="ROLE_USER")
     * @EXT\Template()
     * @EXT\ParamConverter("loggedUser", options={"authenticatedUser" = true})
     */
    public function editPublicProfilePreferencesAction(User $loggedUser)
    {
        $form    = $this->createForm(new UserPublicProfilePreferencesType(), $loggedUser->getPublicProfilePreferences());

        if ($this->request->isMethod('POST')) {
            $form->handleRequest($this->request);

            if ($form->isValid()) {
                /** @var \Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface $sessionFlashBag */
                $sessionFlashBag = $this->get('session')->getFlashBag();
                /** @var \Symfony\Bundle\FrameworkBundle\Translation\Translator $translator */
                $translator = $this->get('translator');

                try {
                    /** @var \Claroline\CoreBundle\Entity\UserPublicProfilePreferences $userPublicProfilePreferences */
                    $userPublicProfilePreferences = $form->getData();

                    if ($userPublicProfilePreferences !== $loggedUser->getPublicProfilePreferences()) {
                        throw new \Exception();
                    }

                    $entityManager = $this->get('doctrine.orm.entity_manager');
                    $entityManager->persist($userPublicProfilePreferences);
                    $entityManager->flush();

                    $sessionFlashBag->add('success', $translator->trans('edit_public_profile_preferences_success', array(), 'platform'));
                } catch(\Exception $exception){
                    $sessionFlashBag->add('error', $translator->trans('edit_public_profile_preferences_error', array(), 'platform'));
                }

                return $this->redirect($this->generateUrl('claro_user_public_profile_preferences'));
            }
        }

        return array(
            'form' => $form->createView(),
            'user' => $loggedUser
        );
    }

    /**
     * @EXT\Route(
     *     "/{publicUrl}",
     *      name="claro_public_profile_view",
     *      options={"expose"=true}
     * )
     */
    public function publicProfileAction($publicUrl)
    {
        /** @var \Claroline\CoreBundle\Entity\User $user */
        $user = $this->getDoctrine()->getRepository('ClarolineCoreBundle:User')->findOneByIdOrPublicUrl($publicUrl);

        if (null === $user) {
            throw $this->createNotFoundException("Unknown user.");
        }

        $userPublicProfilePreferences = $user->getPublicProfilePreferences();
        $publicProfileVisible         = false;

        if ($this->get('security.context')->isGranted('ROLE_ADMIN')) {
            $userPublicProfilePreferences = $this->get('claroline.manager.user_manager')->getUserPublicProfilePreferencesForAdmin();
        }

        $response = new Response($this->renderView('ClarolineCoreBundle:Profile:publicProfile.html.twig', array('user' => $user, 'publicProfilePreferences' => $userPublicProfilePreferences)));


        if(UserPublicProfilePreferences::SHARE_POLICY_NOBODY === $userPublicProfilePreferences->getSharePolicy()) {
            $response = new Response($this->renderView('ClarolineCoreBundle:Profile:publicProfile.404.html.twig', array('user' => $user, 'publicUrl' => $publicUrl)), 404);
        }
        else if (UserPublicProfilePreferences::SHARE_POLICY_PLATFORM_USER === $userPublicProfilePreferences->getSharePolicy()
                 && null === $this->getUser()) {
            $response = new Response($this->renderView('ClarolineCoreBundle:Profile:publicProfile.401.html.twig', array('user' => $user, 'publicUrl' => $publicUrl)), 401);
        }

        return $response;
    }

    /**
     * @EXT\Route(
     *     "/profile/edit/{user}",
     *     name="claro_user_profile_edit"
     * )
     * @SEC\Secure(roles="ROLE_USER")
     *
     * @EXT\Template()
     * @EXT\ParamConverter("loggedUser", options={"authenticatedUser" = true})
     */
    public function editProfileAction(User $loggedUser, User $user = null)
    {
        $isAdmin = $this->get('security.context')->isGranted('ROLE_ADMIN');

        $editYourself = false;

        if (null !== $user && !$isAdmin) {
            throw new AccessDeniedException();
        }

        if (null === $user) {
            $user         = $loggedUser;
            $editYourself = true;
        }

        $roles = $this->roleManager->getRoleByName('ROLE_USER');
        $form = $this->createForm(
            new ProfileType(array($roles), $isAdmin, $this->localeManager->getAvailableLocales()), $user
        );

        $form->handleRequest($this->request);

        if ($form->isValid()) {
            /** @var \Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface $sessionFlashBag */
            $sessionFlashBag = $this->get('session')->getFlashBag();
            /** @var \Symfony\Bundle\FrameworkBundle\Translation\Translator $translator */
            $translator = $this->get('translator');

            $user = $form->getData();
            $this->userManager->rename($user, $user->getUsername());

            $successMessage = $translator->trans('edit_profile_success', array(), 'platform');
            $errorMessage   = $translator->trans('edit_profile_error', array(), 'platform');
            $errorRight = $translator->trans('edit_profile_error_right', array(), 'platform');
            $redirectUrl    = $this->generateUrl('claro_admin_user_list');

            if ($editYourself) {
                $successMessage = $translator->trans('edit_your_profile_success', array(), 'platform');
                $errorMessage   = $translator->trans('edit_your_profile_error', array(), 'platform');
                $redirectUrl    = $this->generateUrl('claro_profile_view');
            }

            $entityManager = $this->getDoctrine()->getManager();
            $unitOfWork    = $entityManager->getUnitOfWork();
            $unitOfWork->computeChangeSets();

            $changeSet = $unitOfWork->getEntityChangeSet($user);
            $newRoles  = array();

            if (isset($form['platformRoles'])) {
                $newRoles = $form['platformRoles']->getData();
                $this->userManager->setPlatformRoles($user, $newRoles);
            }

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
            
            if ($this->userManager->uploadAvatar($user) === false ) {
                $sessionFlashBag->add('error', $errorRight);
            }

            $this->eventDispatcher->dispatch(
                'log',
                'Log\LogUserUpdate',
                array($user, $changeSet)
            );

            $sessionFlashBag->add('success', $successMessage);

            return $this->redirect($redirectUrl);
        }

        return array(
            'form'         => $form->createView(),
            'user'         => $user,
            'editYourself' => $editYourself
        );
    }

    /**
     * @EXT\Route(
     *     "/password/edit",
     *      name="claro_user_password_edit"
     * )
     * @EXT\ParamConverter("loggedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function editPasswordAction(User $loggedUser)
    {
        $form = $this->createForm(new ResetPasswordType(), $loggedUser);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            /** @var \Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface $sessionFlashBag */
            $sessionFlashBag = $this->get('session')->getFlashBag();
            /** @var \Symfony\Bundle\FrameworkBundle\Translation\Translator $translator */
            $translator = $this->get('translator');

            try {
                /** @var \Claroline\CoreBundle\Entity\User $user */
                $user = $form->getData();

                if ($user !== $loggedUser) {
                    throw new \Exception();
                }

                $entityManager = $this->get('doctrine.orm.entity_manager');
                $entityManager->persist($user);
                $entityManager->flush();

                $sessionFlashBag->add('success', $translator->trans('edit_password_success', array(), 'platform'));
            } catch(\Exception $exception){
                $sessionFlashBag->add('error', $translator->trans('edit_password_error', array(), 'platform'));
            }

            return $this->redirect($this->generateUrl('claro_profile_view'));
        }

        return array(
            'form' => $form->createView(),
            'user' => $loggedUser
        );
    }

    /**
     * @EXT\Route(
     *     "/publicurl/edit",
     *      name="claro_user_public_url_edit"
     * )
     * @SEC\Secure(roles="ROLE_USER")
     * @EXT\Template()
     * @EXT\ParamConverter("loggedUser", options={"authenticatedUser" = true})
     */
    public function editPublicUrlAction(User $loggedUser)
    {
        $currentPublicUrl = $loggedUser->getPublicUrl();
        $form = $this->createForm(new UserPublicProfileUrlType(), $loggedUser);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            /** @var \Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface $sessionFlashBag */
            $sessionFlashBag = $this->get('session')->getFlashBag();
            /** @var \Symfony\Bundle\FrameworkBundle\Translation\Translator $translator */
            $translator = $this->get('translator');

            try {
                /** @var \Claroline\CoreBundle\Entity\User $user */
                $user = $form->getData();

                $user->setHasTunedPublicUrl(true);

                $entityManager = $this->get('doctrine.orm.entity_manager');
                $entityManager->persist($user);
                $entityManager->flush();

                $sessionFlashBag->add('success', $translator->trans('tune_public_url_success', array(), 'platform'));
            } catch(\Exception $exception){
                $sessionFlashBag->add('error', $translator->trans('tune_public_url_error', array(), 'platform'));
            }

            return $this->redirect($this->generateUrl('claro_profile_view'));
        }

        return array(
            'form'             => $form->createView(),
            'user'             => $loggedUser,
            'currentPublicUrl' => $currentPublicUrl
        );
    }

    /**
     * @EXT\Route(
     *     "/publicurl/check",
     *      name="claro_user_public_url_check"
     * )
     * @SEC\Secure(roles="ROLE_USER")
     * @EXT\Method({"POST"})
     */
    public function checkPublicUrlAction(Request $request)
    {
        $existedUser = $this->getDoctrine()->getRepository('ClarolineCoreBundle:User')->findOneByPublicUrl($request->request->get('publicUrl'));
        $data = array('check' => false);

        if (null === $existedUser) {
            $data['check'] = true;
        }

        $response = new JsonResponse($data);
        return $response;
    }
}
