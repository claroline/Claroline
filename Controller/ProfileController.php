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
     *     "/{publicUrl}",
     *      name="claro_profile_view"
     * )
     * @EXT\Template()
     */
    public function viewAction($publicUrl)
    {
        $user = $this->getDoctrine()->getRepository('ClarolineCoreBundle:User')->findOneByPublicUrl($publicUrl);
        if (null === $user) {
            throw $this->createNotFoundException("Unknown user.");
        }

        return array(
            'user'  => $user
        );
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

        if (null === $user) {
            $user         = $loggedUser;
            $editYourself = true;
        }

        if ($user !== $loggedUser && !$isAdmin) {
            throw new AccessDeniedException();
        }

        $roles = $this->roleManager->getPlatformRoles($loggedUser);
        $form = $this->createForm(
            new ProfileType($roles, $isAdmin, $this->localeManager->getAvailableLocales()), $user
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
            $redirectUrl    = $this->generateUrl('claro_admin_user_list');
            if ($editYourself) {
                $successMessage = $translator->trans('edit_your_profile_success', array(), 'platform');
                $errorMessage   = $translator->trans('edit_your_profile_error', array(), 'platform');
                $redirectUrl    = $this->generateUrl('claro_profile_view', array('publicUrl' => $user->getPublicUrl()));
            }

            try {
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

                $this->userManager->uploadAvatar($user);
                $this->eventDispatcher->dispatch(
                    'log',
                    'Log\LogUserUpdate',
                    array($user, $changeSet)
                );

                $sessionFlashBag->add('success', $successMessage);
            } catch(\Exception $exception){
                $sessionFlashBag->add('error', $errorMessage);
            }

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
     *     "/password/edit/{user}",
     *      name="claro_user_password_edit"
     * )
     * @SEC\Secure(roles="ROLE_USER")
     * @EXT\Template()
     * @EXT\ParamConverter("loggedUser", options={"authenticatedUser" = true})
     */
    public function editPasswordAction(User $loggedUser, User $user = null)
    {
        if (null === $user) {
            $user = $loggedUser;
        }

        if ($user !== $loggedUser && !$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(new ResetPasswordType(), $user);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            /** @var \Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface $sessionFlashBag */
            $sessionFlashBag = $this->get('session')->getFlashBag();
            /** @var \Symfony\Bundle\FrameworkBundle\Translation\Translator $translator */
            $translator = $this->get('translator');

            try {
                /** @var \Claroline\CoreBundle\Entity\User $user */
                $user = $form->getData();

                $entityManager = $this->get('doctrine.orm.entity_manager');
                $entityManager->persist($user);
                $entityManager->flush();

                $sessionFlashBag->add('success', $translator->trans('edit_password_success', array(), 'platform'));
            } catch(\Exception $exception){
                $sessionFlashBag->add('error', $translator->trans('edit_password_error', array(), 'platform'));
            }

            return $this->redirect($this->generateUrl('claro_profile_view', array('publicUrl' => $user->getPublicUrl())));
        }

        return array(
            'form' => $form->createView(),
            'user' => $user
        );
    }
}
