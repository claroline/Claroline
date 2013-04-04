<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Form\ProfileType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * Controller of the user profile.
 */
class ProfileController extends Controller
{
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
            $newRoles = $form->get('platformRoles')->getData();
            $userRole = $this->get('doctrine.orm.entity_manager')
                ->getRepository('ClarolineCoreBundle:Role')
                ->findOneByName('ROLE_USER');

            foreach ($roles as $role) {
                if ($role !== $userRole) {
                    $user->removeRole($role);
                }
            }
            foreach ($newRoles as $role) {
                $user->addRole($role);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            $this->get('security.context')->getToken()->setUser($user);
            
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
}