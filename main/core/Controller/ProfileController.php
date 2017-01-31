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
use Claroline\CoreBundle\Event\DisplayToolEvent;
use Claroline\CoreBundle\Event\Profile\ProfileLinksEvent;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Form\ProfileType;
use Claroline\CoreBundle\Form\ResetPasswordType;
use Claroline\CoreBundle\Form\UserPublicProfileUrlType;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\AuthenticationManager;
use Claroline\CoreBundle\Manager\FacetManager;
use Claroline\CoreBundle\Manager\LocaleManager;
use Claroline\CoreBundle\Manager\ProfilePropertyManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\CoreBundle\Manager\UserManager;
use Doctrine\ORM\NoResultException;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Controller of the user profile.
 */
class ProfileController extends Controller
{
    private $userManager;
    private $roleManager;
    private $eventDispatcher;
    private $tokenStorage;
    private $request;
    private $localeManager;
    private $encoderFactory;
    private $toolManager;
    private $facetManager;
    private $ch;
    private $authenticationManager;
    private $profilePropertyManager;

    /**
     * @DI\InjectParams({
     *     "userManager"            = @DI\Inject("claroline.manager.user_manager"),
     *     "roleManager"            = @DI\Inject("claroline.manager.role_manager"),
     *     "eventDispatcher"        = @DI\Inject("claroline.event.event_dispatcher"),
     *     "tokenStorage"           = @DI\Inject("security.token_storage"),
     *     "request"                = @DI\Inject("request"),
     *     "localeManager"          = @DI\Inject("claroline.manager.locale_manager"),
     *     "encoderFactory"         = @DI\Inject("security.encoder_factory"),
     *     "toolManager"            = @DI\Inject("claroline.manager.tool_manager"),
     *     "facetManager"           = @DI\Inject("claroline.manager.facet_manager"),
     *     "ch"                     = @DI\Inject("claroline.config.platform_config_handler"),
     *     "authenticationManager"  = @DI\Inject("claroline.common.authentication_manager"),
     *     "profilePropertyManager" = @DI\Inject("claroline.manager.profile_property_manager")
     * })
     */
    public function __construct(
        UserManager $userManager,
        RoleManager $roleManager,
        StrictDispatcher $eventDispatcher,
        TokenStorageInterface $tokenStorage,
        Request $request,
        LocaleManager $localeManager,
        EncoderFactory $encoderFactory,
        ToolManager $toolManager,
        FacetManager $facetManager,
        PlatformConfigurationHandler $ch,
        AuthenticationManager $authenticationManager,
        ProfilePropertyManager $profilePropertyManager
    ) {
        $this->userManager = $userManager;
        $this->roleManager = $roleManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->tokenStorage = $tokenStorage;
        $this->request = $request;
        $this->localeManager = $localeManager;
        $this->encoderFactory = $encoderFactory;
        $this->toolManager = $toolManager;
        $this->facetManager = $facetManager;
        $this->ch = $ch;
        $this->authenticationManager = $authenticationManager;
        $this->profilePropertyManager = $profilePropertyManager;
    }

    private function isInRoles($role, $roles)
    {
        foreach ($roles as $current) {
            if ($role->getId() === $current->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @EXT\Route(
     *     "/{publicUrl}",
     *      name="claro_public_profile_view",
     *      options={"expose"=true}
     * )
     * @EXT\Template()
     */
    public function publicProfileAction(Request $request, $publicUrl)
    {
        $isAccessibleForAnon = $this->ch->getParameter('anonymous_public_profile');

        if (!$isAccessibleForAnon && $this->tokenStorage->getToken()->getUser() === 'anon.') {
            throw new AccessDeniedException();
        }

        try {
            $user = $this->getDoctrine()->getRepository('ClarolineCoreBundle:User')->findOneByIdOrPublicUrl($publicUrl);

            return ['user' => $user];
        } catch (NoResultException $e) {
            throw new NotFoundHttpException('Page not found');
        }
    }

    /**
     * @EXT\Route(
     *     "/show/{user}",
     *      name="claro_profile_view",
     *      options={"expose"=true}
     * )
     * @SEC\Secure(roles="ROLE_USER")
     * @EXT\Template("ClarolineCoreBundle:Profile:publicProfile.html.twig")
     */
    public function viewAction(Request $request, User $user)
    {
        return $this->publicProfileAction($request, $user->getPublicUrl());
    }

    /**
     * @EXT\Template()
     */
    public function myProfileWidgetAction(Request $request)
    {
        $user = $this->tokenStorage->getToken()->getUser();

        if ($user === 'anon.') {
            return ['isAnon' => true];
        } else {
            $facets = $this->facetManager->getVisibleFacets(5);
            $fieldFacetValues = $this->facetManager->getFieldValuesByUser($user);
            $fieldFacets = $this->facetManager->getVisibleFieldForCurrentUserFacets();
            $profileLinksEvent = new ProfileLinksEvent($user, $request->getLocale());
            $publicProfilePreferences = $this->facetManager->getVisiblePublicPreference();
            $this->get('event_dispatcher')->dispatch(
                'profile_link_event',
                $profileLinksEvent
            );
            $dektopBadgesEvent = new DisplayToolEvent();
            $this->get('event_dispatcher')->dispatch(
                'list_all_my_badges',
                $dektopBadgesEvent
            );

            //Test profile completeness
            $totalVisibleFields = count($fieldFacets);
            $totalFilledVisibleFields = count(array_filter($fieldFacetValues));
            if ($publicProfilePreferences['baseData']) {
                ++$totalVisibleFields;
                if (!empty($user->getDescription())) {
                    ++$totalFilledVisibleFields;
                }
            }
            if ($publicProfilePreferences['phone']) {
                ++$totalVisibleFields;
                if (!empty($user->getPhone())) {
                    ++$totalFilledVisibleFields;
                }
            }

            $completion = $totalVisibleFields === 0 ? null : round($totalFilledVisibleFields / $totalVisibleFields * 100);
            $links = $profileLinksEvent->getLinks();

            return [
                'user' => $user,
                'publicProfilePreferences' => $publicProfilePreferences,
                'facets' => $facets,
                'fieldFacetValues' => $fieldFacetValues,
                'fieldFacets' => $fieldFacets,
                'links' => $links,
                'badges' => $dektopBadgesEvent->getContent(),
                'completion' => $completion,
                'isAnon' => false,
            ];
        }
    }

    /**
     * @EXT\Route(
     *     "/profile/edit/{user}",
     *     name="claro_user_profile_edit",
     *     options={"expose"=true}
     * )
     * @SEC\Secure(roles="ROLE_USER")
     *
     * @EXT\Template()
     * @EXT\ParamConverter("loggedUser", options={"authenticatedUser" = true})
     */
    public function editProfileAction(User $loggedUser, User $user = null)
    {
        $isAdmin = $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN');
        $isGrantedUserAdmin = $this->get('security.authorization_checker')->isGranted(
            'OPEN', $this->toolManager->getAdminToolByName('user_management')
        );

        if (null === $user) {
            $user = $loggedUser;
        }

        $editYourself = $user->getId() === $loggedUser->getId();

        if (null !== $user && !$isAdmin && !$isGrantedUserAdmin && !$editYourself) {
            throw new AccessDeniedException();
        }

        $roles = $this->roleManager->getPlatformRoles($user);
        $accesses = $this->profilePropertyManager->getAccessesForCurrentUser();

        $profileType = new ProfileType(
            $this->localeManager,
            $roles,
            $isAdmin,
            $isGrantedUserAdmin,
            $accesses,
            $this->authenticationManager->getDrivers()
        );

        $form = $this->createForm($profileType, $user);
        $form->handleRequest($this->request);
        $unavailableRoles = [];

        if ($this->get('request')->getMethod() === 'POST') {
            $roles = ($isAdmin || $isGrantedUserAdmin) ?
                $form->get('platformRoles')->getData() :
                [$this->roleManager->getRoleByName('ROLE_USER')];
        } else {
            $roles = ($isAdmin || $isGrantedUserAdmin) ?
                $this->roleManager->getAllPlatformRoles() :
                [$this->roleManager->getRoleByName('ROLE_USER')];
        }

        foreach ($roles as $role) {
            $isAvailable = $this->roleManager->validateRoleInsert($user, $role);
            if (!$isAvailable) {
                $unavailableRoles[] = $role;
            }
        }

        if ($form->isValid() && count($unavailableRoles) === 0) {
            /** @var \Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface $sessionFlashBag */
            $sessionFlashBag = $this->get('session')->getFlashBag();
            /** @var \Symfony\Component\Translation\TranslatorInterface $translator */
            $translator = $this->get('translator');

            $user = $form->getData();
            $this->userManager->rename($user, $user->getUsername());

            $successMessage = $translator->trans('edit_profile_success', [], 'platform');
            $errorMessage = $translator->trans('edit_profile_error', [], 'platform');
            $errorRight = $translator->trans('edit_profile_error_right', [], 'platform');
            $redirectUrl = $this->generateUrl('claro_admin_users_index');

            if ($editYourself) {
                $successMessage = $translator->trans('edit_your_profile_success', [], 'platform');
                $errorMessage = $translator->trans('edit_your_profile_error', [], 'platform');
                $redirectUrl = $this->generateUrl('claro_public_profile_view', ['publicUrl' => $user->getPublicUrl()]);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $unitOfWork = $entityManager->getUnitOfWork();
            $unitOfWork->computeChangeSets();

            $changeSet = $unitOfWork->getEntityChangeSet($user);
            $newRoles = [];

            if (isset($form['platformRoles'])) {
                //verification:
                //only the admin can grant the role admin
                //simple users cannot change anything. Don't let them put whatever they want with a fake form.
                $newRoles = $form['platformRoles']->getData();
                $this->userManager->setPlatformRoles($user, $newRoles);
            }

            $rolesChangeSet = [];
            //Detect added
            foreach ($newRoles as $role) {
                if (!$this->isInRoles($role, $roles)) {
                    $rolesChangeSet[$role->getTranslationKey()] = [false, true];
                }
            }
            //Detect removed
            foreach ($roles as $role) {
                if (!$this->isInRoles($role, $newRoles)) {
                    $rolesChangeSet[$role->getTranslationKey()] = [true, false];
                }
            }
            if (count($rolesChangeSet) > 0) {
                $changeSet['roles'] = $rolesChangeSet;
            }

            if ($this->userManager->uploadAvatar($user) === false) {
                $sessionFlashBag->add('error', $errorRight);
            }

            $this->eventDispatcher->dispatch(
                'log',
                'Log\LogUserUpdate',
                [$user, $changeSet]
            );

            $sessionFlashBag->add('success', $successMessage);

            return $this->redirect($redirectUrl);
        }

        return [
            'form' => $form->createView(),
            'user' => $user,
            'editYourself' => $editYourself,
            'unavailableRoles' => $unavailableRoles,
        ];
    }

    /**
     * @EXT\Route(
     *     "/password/edit/{user}",
     *      name="claro_user_password_edit"
     * )
     * @EXT\ParamConverter("loggedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function editPasswordAction(User $user, User $loggedUser)
    {
        $isAdmin = $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN');
        $isGrantedUserAdmin = $this->get('security.authorization_checker')->isGranted(
            'OPEN', $this->toolManager->getAdminToolByName('user_management')
        );
        $selfEdit = $user->getId() === $loggedUser->getId() ? true : false;

        if (!$selfEdit && !$isAdmin && !$isGrantedUserAdmin) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(new ResetPasswordType($selfEdit));
        $oldPassword = $user->getPassword();
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            /** @var \Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface $sessionFlashBag */
            $sessionFlashBag = $this->get('session')->getFlashBag();
            /** @var \Symfony\Component\Translation\TranslatorInterface $translator */
            $translator = $this->get('translator');
            $continue = !$selfEdit;

            if ($selfEdit) {
                $user->setPlainPassword($form['password']->getData());
            }

            if ($selfEdit && $this->encodePassword($user) === $oldPassword) {
                $continue = true;
            }

            if ($continue) {
                $user->setPlainPassword($form['plainPassword']->getData());
                $user->setPassword($this->encodePassword($user));
                $entityManager = $this->get('doctrine.orm.entity_manager');
                $entityManager->persist($user);
                $entityManager->flush();
                $sessionFlashBag->add('success', $translator->trans('edit_password_success', [], 'platform'));
            } else {
                $sessionFlashBag->add('error', $translator->trans('edit_password_error_current', [], 'platform'));
            }

            if ($selfEdit) {
                return $this->redirect($this->generateUrl('claro_public_profile_view', ['publicUrl' => $user->getPublicUrl()]));
            } else {
                return $this->redirect($this->generateUrl('claro_admin_users_index'));
            }
        }

        return [
            'form' => $form->createView(),
            'user' => $user,
        ];
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
            /** @var \Symfony\Component\Translation\TranslatorInterface $translator */
            $translator = $this->get('translator');

            try {
                /** @var \Claroline\CoreBundle\Entity\User $user */
                $user = $form->getData();

                $user->setHasTunedPublicUrl(true);

                $entityManager = $this->get('doctrine.orm.entity_manager');
                $entityManager->persist($user);
                $entityManager->flush();

                $sessionFlashBag->add('success', $translator->trans('tune_public_url_success', [], 'platform'));
            } catch (\Exception $exception) {
                $sessionFlashBag->add('error', $translator->trans('tune_public_url_error', [], 'platform'));
            }

            return $this->redirect($this->generateUrl('claro_public_profile_view', ['publicUrl' => $user->getPublicUrl()]));
        }

        return [
            'form' => $form->createView(),
            'user' => $loggedUser,
            'currentPublicUrl' => $currentPublicUrl,
        ];
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
        $publicUrl = $request->request->get('publicUrl');
        $data = ['check' => false];
        if (preg_match('/^[^\/]+$/', $publicUrl)) {
            $existedUser = $this->getDoctrine()->getRepository('ClarolineCoreBundle:User')->findOneByPublicUrl($publicUrl);
            if (null === $existedUser) {
                $data['check'] = true;
            }
        }

        $response = new JsonResponse($data);

        return $response;
    }

    private function encodePassword(User $user)
    {
        return $this->encoderFactory
            ->getEncoder($user)
            ->encodePassword($user->getPlainPassword(), $user->getSalt());
    }
}
