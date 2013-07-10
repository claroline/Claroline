<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Form\ProfileType;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Event\Log\LogUserUpdateEvent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Controller of the user profile.
 */
class ProfileController extends Controller
{
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
     * @Route(
     *     "/form",
     *     name="claro_profile_form"
     * )
     *
     * @Template("ClarolineCoreBundle:Profile:profileForm.html.twig")
     *
     * Displays an editable form of the current user's profile.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function formAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $roles = $this->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Role')
            ->findPlatformRoles($user);
        $form = $this->createForm(new ProfileType($roles), $user);

        return array('profile_form' => $form->createView());
    }

    /**
     * @Route(
     *     "/update",
     *     name="claro_profile_update"
     * )
     *
     * @Template("ClarolineCoreBundle:Profile:profileForm.html.twig")
     *
     * Updates the user's profile and redirects to the profile form.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateAction()
    {
        $request = $this->get('request');
        $user = $this->get('security.context')->getToken()->getUser();
        $roles = $this->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Role')
            ->findPlatformRoles($user);
        $form = $this->get('form.factory')->create(new ProfileType($roles), $user);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $roleManager = $this->get('claroline.manager.role_manager');
            $user = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $unitOfWork = $em->getUnitOfWork();
            $unitOfWork->computeChangeSets();
            $changeSet = $unitOfWork->getEntityChangeSet($user);
            $newRoles = $form->get('platformRoles')->getData();

            $roleManager->resetRoles($user);
            $roleManager->associateRoles($user, $newRoles);
            $this->get('security.context')->getToken()->setUser($user);

            $newRoles = $this->get('doctrine.orm.entity_manager')
                ->getRepository('ClarolineCoreBundle:Role')
                ->findPlatformRoles($user);

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

            $log = $this->get('claroline.event.event_dispatcher')->dispatch(
                'log',
                'Log\UserUpdateEvent',
                array($user,$changeSet)
            );

            return $this->redirect($this->generateUrl('claro_profile_form'));
        }

        return array('profile_form' => $form->createView());
    }

    /**
     * @Route(
     *     "/view/{userId}",
     *     name="claro_profile_view"
     * )
     *
     * @Template("ClarolineCoreBundle:Profile:profile.html.twig")
     *
     * Displays the public profile of an user.
     *
     * @param integer $userId The id of the user we want to see the profile
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewAction($userId)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('ClarolineCoreBundle:User')->find($userId);

        return array('user' => $user);
    }
}