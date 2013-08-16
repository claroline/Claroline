<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Claroline\CoreBundle\Form\ProfileType;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Event\Log\LogUserUpdateEvent;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;

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

    /**
     * @DI\InjectParams({
     *     "userManager"        = @DI\Inject("claroline.manager.user_manager"),
     *     "roleManager"        = @DI\Inject("claroline.manager.role_manager"),
     *     "eventDispatcher"    = @DI\Inject("claroline.event.event_dispatcher"),
     *     "security"           = @DI\Inject("security.context")
     * })
     */
    public function __construct(
        UserManager $userManager,
        RoleManager $roleManager,
        StrictDispatcher $eventDispatcher,
        SecurityContextInterface $security
    )
    {
        $this->userManager = $userManager;
        $this->roleManager = $roleManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->security = $security;
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
     *     "/form",
     *     name="claro_profile_form"
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Profile:profileForm.html.twig")
     *
     * Displays an editable form of the current user's profile.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function formAction()
    {
        $user = $this->security->getToken()->getUser();
        $roles = $this->roleManager->getPlatformRoles($user);
        $form = $this->createForm(new ProfileType($roles), $user);

        return array('profile_form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "/update",
     *     name="claro_profile_update"
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Profile:profileForm.html.twig")
     *
     * Updates the user's profile and redirects to the profile form.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateAction()
    {
        $request = $this->get('request');
        $user = $this->security->getToken()->getUser();
        $roles = $this->roleManager->getPlatformRoles($user);

        $form = $this->get('form.factory')->create(new ProfileType($roles), $user);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $user = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $unitOfWork = $em->getUnitOfWork();
            $unitOfWork->computeChangeSets();
            $changeSet = $unitOfWork->getEntityChangeSet($user);
            $newRoles = $form->get('platformRoles')->getData();

            $this->roleManager->resetRoles($user);
            $this->roleManager->associateRoles($user, $newRoles);
            $this->security->getToken()->setUser($user);

            $newRoles = $this->roleManager->getPlatformRoles($user);

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

            $this->eventDispatcher->dispatch(
                'log',
                'Log\LogUserUpdateEvent',
                array($user, $changeSet)
            );

            return $this->redirect($this->generateUrl('claro_profile_form'));
        }

        return array('profile_form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "/view/{userId}",
     *     name="claro_profile_view"
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
     * @param integer $userId The id of the user we want to see the profile
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewAction(User $user)
    {
        return array('user' => $user);
    }
}
