<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Entity\Badge\Badge;
use Claroline\CoreBundle\Entity\Badge\BadgeTranslation;
use Claroline\CoreBundle\Entity\Badge\UserBadge;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Form\BadgeAwardType;
use Claroline\CoreBundle\Form\BadgeType;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Library\Event\DisplayWidgetEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Claroline\CoreBundle\Library\Event\DisplayToolEvent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * Controller of the badges.
 *
 * @Route("/admin/badges")
 */
class BadgeController extends Controller
{
    /**
     * @Route("/", name="claro_admin_badges")
     *
     * @Template()
     */
    public function listAction()
    {
        if (!$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            throw new \AccessDeniedException();
        }

        /** @var \Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler $platformConfigHandler */
        $platformConfigHandler = $this->get('claroline.config.platform_config_handler');

        /** @var \Claroline\CoreBundle\Repository\BadgeRepository $badgeRepository */
        $badgeRepository = $this->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Badge\Badge');

        /** @var Badge[] $badges */
        $badges = $badgeRepository->findAllOrderedByName($platformConfigHandler->getParameter('locale_language'));

        foreach($badges as $badge)
        {
            $badge->setLocale($platformConfigHandler->getParameter('locale_language'));
        }

        return array(
            'badges' => $badges
        );
    }

    /**
     * @Route("/add", name="claro_admin_badges_add")
     *
     * @Template()
     */
    public function addAction(Request $request)
    {
        if (!$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            throw new \AccessDeniedException();
        }

        $badge = new Badge();

        //@TODO Get locales from locale source (database etc...)
        $locales = array('fr', 'en');
        foreach($locales as $locale)
        {
            $translation = new BadgeTranslation();
            $translation->setLocale($locale);
            $badge->addTranslation($translation);
        }

        /** @var \Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler $platformConfigHandler */
        $platformConfigHandler = $this->get('claroline.config.platform_config_handler');

        $form = $this->createForm(new BadgeType(), $badge, array('language' => $platformConfigHandler->getParameter('locale_language')));

        if($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                /** @var \Symfony\Bundle\FrameworkBundle\Translation\Translator $translator */
                $translator = $this->get('translator');
                try {
                    /** @var \Doctrine\Common\Persistence\ObjectManager $entityManager */
                    $entityManager = $this->getDoctrine()->getManager();

                    $entityManager->persist($badge);
                    $entityManager->flush();

                    $this->get('session')->getFlashBag()->add('success', $translator->trans('badge_add_success_message', array(), 'platform'));
                }
                catch(\Exception $exception) {
                    $this->get('session')->getFlashBag()->add('error', $translator->trans('badge_add_error_message', array(), 'platform'));
                }

                return $this->redirect($this->generateUrl('claro_admin_badges'));
            }
        }

        return array(
            'form' => $form->createView()
        );
    }

    /**
     * @Route("/edit/{id}/{page}", name="claro_admin_badges_edit")
     *
     * @Template()
     */
    public function editAction(Request $request, Badge $badge, $page = 1)
    {
        if (!$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            throw new \AccessDeniedException();
        }

        /** @var \Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler $platformConfigHandler */
        $platformConfigHandler = $this->get('claroline.config.platform_config_handler');
        $badge->setLocale($platformConfigHandler->getParameter('locale_language'));

        $doctrine = $this->getDoctrine();

        $query   = $doctrine->getRepository('ClarolineCoreBundle:Badge\Badge')->findUsers($badge, true);
        $adapter = new DoctrineORMAdapter($query);
        $pager   = new Pagerfanta($adapter);
        $pager
            ->setMaxPerPage(10)
            ->setCurrentPage($page)
        ;

        $form = $this->createForm(new BadgeType(), $badge);

        if($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                /** @var \Symfony\Bundle\FrameworkBundle\Translation\Translator $translator */
                $translator = $this->get('translator');
                try {
                    /** @var \Doctrine\Common\Persistence\ObjectManager $entityManager */
                    $entityManager = $doctrine->getManager();

                    $entityManager->persist($badge);
                    $entityManager->flush();

                    $this->get('session')->getFlashBag()->add('success', $translator->trans('badge_edit_success_message', array(), 'platform'));
                }
                catch(\Exception $exception) {
                    $this->get('session')->getFlashBag()->add('error', $translator->trans('badge_edit_error_message', array(), 'platform'));
                }

                return $this->redirect($this->generateUrl('claro_admin_badges'));
            }
        }

        return array(
            'form'  => $form->createView(),
            'badge' => $badge,
            'pager' => $pager
        );
    }

    /**
     * @Route("/delete/{id}", name="claro_admin_badges_delete")
     *
     * @Template()
     */
    public function deleteAction(Badge $badge)
    {
        if (!$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            throw new \AccessDeniedException();
        }

        /** @var \Symfony\Bundle\FrameworkBundle\Translation\Translator $translator */
        $translator = $this->get('translator');
        try {
            /** @var \Doctrine\Common\Persistence\ObjectManager $entityManager */
            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->remove($badge);
            $entityManager->flush();

            $this->get('session')->getFlashBag()->add('success', $translator->trans('badge_delete_success_message', array(), 'platform'));
        }
        catch(\Exception $exception) {
            $this->get('session')->getFlashBag()->add('error', $translator->trans('badge_delete_error_message', array(), 'platform'));
        }

        return $this->redirect($this->generateUrl('claro_admin_badges'));
    }

    /**
     * @Route("/award/{id}", name="claro_admin_badges_award")
     *
     * @Template()
     */
    public function awardAction(Request $request, Badge $badge)
    {
        if (!$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            throw new \AccessDeniedException();
        }

        $form = $this->createForm(new BadgeAwardType());

        if($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                /** @var \Symfony\Bundle\FrameworkBundle\Translation\Translator $translator */
                $translator = $this->get('translator');
                try {
                    $doctrine = $this->getDoctrine();

                    /** @var \Doctrine\ORM\EntityManager $entityManager */
                    $entityManager = $doctrine->getManager();

                    $groupName    = $form->get('group')->getData();
                    $userName     = $form->get('user')->getData();
                    $awardedBadge = 0;

                    /** @var \Claroline\CoreBundle\Entity\User[] $users */
                    $users = array();

                    if(null !== $groupName) {
                        $group = $doctrine->getRepository('ClarolineCoreBundle:Group')->findOneByName($groupName);

                        if(null !== $group) {
                            $users = $doctrine->getRepository('ClarolineCoreBundle:User')->findByGroup($group);
                        }
                    }
                    elseif(null !== $userName) {
                        list($firstName, $lastName) = explode(' ', $userName);
                        $user = $doctrine->getRepository('ClarolineCoreBundle:User')->findOneByUsername($firstName . $lastName);

                        if(null !== $user) {
                            $users[] = $user;
                        }
                    }

                    foreach($users as $user)
                    {
                        if(!$user->hasBadge($badge)) {
                            $awardedBadge++;

                            $userBadge = new UserBadge();
                            $userBadge
                            ->setBadge($badge)
                            ->setUser($user)
                            ;
                            $badge->addUserBadge($userBadge);
                        }
                    }

                    $flashMessageType = 'alert';

                    if(0 < $awardedBadge) {
                        $entityManager->persist($badge);
                        $entityManager->flush();

                        $flashMessageType = 'success';
                    }

                    $this->get('session')->getFlashBag()->add($flashMessageType, $translator->transChoice('badge_awarded_count_message', $awardedBadge, array('%awaredBadge%' => $awardedBadge), 'platform'));
                }
                catch(\Exception $exception) {
                    if(!$request->isXmlHttpRequest()) {
                        $this->get('session')->getFlashBag()->add('error', $translator->trans('badge_award_error_message', array(), 'platform'));
                    }
                    else {
                        return new Response($exception->getMessage(), 500);
                    }
                }

                if($request->isXmlHttpRequest()) {
                    return new JsonResponse(array('error' => false));
                }

                return $this->redirect($this->generateUrl('claro_admin_badges_edit', array('id' => $badge->getId())));
            }
        }

        return array(
            'badge'  => $badge,
            'form'   => $form->createView()
        );
    }

    /**
     * @Route("/unaward/{id}/{username}", name="claro_admin_badges_unaward")
     * @ParamConverter("user", options={"mapping": {"username": "username"}})
     *
     * @Template()
     */
    public function unawardAction(Request $request, Badge $badge, User $user)
    {
        if (!$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            throw new \AccessDeniedException();
        }

        /** @var \Symfony\Bundle\FrameworkBundle\Translation\Translator $translator */
        $translator = $this->get('translator');
        try {

            $nbRows = $this->getDoctrine()->getRepository('ClarolineCoreBundle:Badge\UserBadge')->deleteByBadgeAndUser($badge, $user);

            if(0 == $nbRows) {
                throw new \InvalidArgumentException('No awarded badge deletion occured.');
            }

            $this->get('session')->getFlashBag()->add('success', $translator->trans('badge_unaward_success_message', array(), 'platform'));
        }
        catch(\Exception $exception) {
            if(!$request->isXmlHttpRequest()) {
                $this->get('session')->getFlashBag()->add('error', $translator->trans('badge_unaward_error_message', array(), 'platform'));
            }
            else {
                return new Response($exception->getMessage(), 500);
            }
        }

        if($request->isXmlHttpRequest()) {
            return new JsonResponse(array('error' => false));
        }

        return $this->redirect($this->generateUrl('claro_admin_badges_edit', array('id' => $badge->getId())));
    }
}