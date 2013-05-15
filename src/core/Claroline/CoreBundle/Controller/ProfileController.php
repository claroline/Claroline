<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Form\ProfileType;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Event\LogUserUpdateEvent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

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

        return $this->render(
            'ClarolineCoreBundle:Profile:profile_form.html.twig',
            array('profile_form' => $form->createView())
        );
    }

    /**
     * @Route(
     *     "/update",
     *     name="claro_profile_update"
     * )
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
        $form->bind($request);

        if ($form->isValid()) {

            $user = $form->getData();

            $em = $this->getDoctrine()->getEntityManager();
            $unitOfWork = $em->getUnitOfWork();
            $unitOfWork->computeChangeSets();
            $changeSet = $unitOfWork->getEntityChangeSet($user);
            $newRoles = $form->get('platformRoles')->getData();
            $user = $this->resetRoles($user);
            $user = $this->addRoles($user, $newRoles);
            $em->persist($user);
            $em->flush();
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

            $log = new LogUserUpdateEvent($user, $changeSet);
            $this->get('event_dispatcher')->dispatch('log', $log);

            return $this->redirect($this->generateUrl('claro_profile_form'));
        }

        return $this->render(
            'ClarolineCoreBundle:Profile:profile_form.html.twig',
            array('profile_form' => $form->createView())
        );
    }

    /**
     * @Route(
     *     "/view/{userId}",
     *     name="claro_profile_view"
     * )
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

        return $this->render(
            'ClarolineCoreBundle:Profile:profile.html.twig',
            array('user' => $user)
        );
    }

    private function addRoles(User $user, $newRoles)
    {
        foreach ($newRoles as $role) {
            $user->addRole($role);
        }

        return $user;
    }

    private function resetRoles(User $user)
    {
        $userRole = $this->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Role')
            ->findOneByName('ROLE_USER');

        $roles = $this->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Role')
            ->findPlatformRoles($user);

        foreach ($roles as $role) {
            if ($role !== $userRole) {
                $user->removeRole($role);
            }
        }

        return $user;
    }
}