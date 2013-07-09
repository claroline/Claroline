<?php

namespace Claroline\CoreBundle\Controller;

use Doctrine\ORM\NoResultException;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Form\ProfileType;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Event\LogUserUpdateEvent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

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

            $user = $form->getData();

            $em = $this->getDoctrine()->getManager();
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

        return array('profile_form' => $form->createView());
    }

    /**
     * @Route(
     *     "/view/{userId}/{page}",
     *           name="claro_profile_view"
     * )
     *
     * @Template("ClarolineCoreBundle:Profile:profile.html.twig")
     * @ParamConverter("user", class="ClarolineCoreBundle:User", options={"id" = "userId"})
     *
     * Displays the public profile of an user.
     *
     * @param \Claroline\CoreBundle\Entity\User $user
     *
     * @param int                               $page
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewAction(User $user, $page = 1)
    {
        $query = $this->getDoctrine()->getRepository('ClarolineCoreBundle:Badge\Badge')->findByUser($user, true);
        $adapter = new DoctrineORMAdapter($query);
        $pager   = new Pagerfanta($adapter);
        $pager
            ->setMaxPerPage(10)
            ->setCurrentPage($page)
        ;

        return array(
            'user'  => $user,
            'pager' => $pager
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

    /**
     * @Route("/badges/{page}", name="claro_profile_view_badges")
     *
     * @Template("ClarolineCoreBundle:Profile:badge.html.twig")
     *
     * Displays the public profile of an user.
     *
     * @param int $page
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function badgeAction($page = 1)
    {
        $user = $this->get('security.context')->getToken()->getUser();

        $query = $this->getDoctrine()->getRepository('ClarolineCoreBundle:Badge\Badge')->findByUser($user, true);
        $adapter = new DoctrineORMAdapter($query);
        $pager   = new Pagerfanta($adapter);
        $pager
            ->setMaxPerPage(10)
            ->setCurrentPage($page)
        ;

        return array(
            'pager' => $pager
        );
    }
}