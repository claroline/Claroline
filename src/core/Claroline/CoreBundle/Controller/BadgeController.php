<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Entity\Badge\Badge;
use Claroline\CoreBundle\Entity\Badge\BadgeTranslation;
use Claroline\CoreBundle\Form\BadgeAttributionType;
use Claroline\CoreBundle\Form\BadgeType;
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

        $form = $this->createForm(new BadgeType(), $badge);

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
     * @Route("/edit/{id}", name="claro_admin_badges_edit")
     *
     * @Template()
     */
    public function editAction(Request $request, Badge $badge)
    {
        if (!$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            throw new \AccessDeniedException();
        }

        /** @var \Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler $platformConfigHandler */
        $platformConfigHandler = $this->get('claroline.config.platform_config_handler');

        $badge->setLocale($platformConfigHandler->getParameter('locale_language'));

        $locales = array('fr', 'en');

        $form = $this->createForm(new BadgeType(), $badge);

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
            'badge' => $badge
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
     * @Route("/attribute/{id}", name="claro_admin_badges_attribute")
     *
     * @Template()
     */
    public function attributeAction(Request $request, Badge $badge)
    {
        if (!$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            throw new \AccessDeniedException();
        }

        $doctrine = $this->getDoctrine();
        $users    = $doctrine->getRepository('ClarolineCoreBundle:User')->findAll();
        $groups   = $doctrine->getRepository('ClarolineCoreBundle:Group')->findAll();

        $form = $this->createForm(new BadgeAttributionType(), $badge);

        if($request->isMethod('POST')) {
            $previousUserBadges = $badge->getUserBadge()->toArray();

            $form->handleRequest($request);
            if ($form->isValid()) {
                /** @var \Symfony\Bundle\FrameworkBundle\Translation\Translator $translator */
                $translator = $this->get('translator');
                try {
                    /** @var \Doctrine\ORM\EntityManager $entityManager */
                    $entityManager = $doctrine->getManager();

                    $doctrine->getRepository('ClarolineCoreBundle:Badge\UserBadge')->deleteBybadge($badge);

                    $entityManager->persist($badge);
                    $entityManager->flush();

                    $this->get('session')->getFlashBag()->add('success', $translator->trans('badge_attribution_success_message', array(), 'platform'));
                }
                catch(\Exception $exception) {
                    if(!$request->isXmlHttpRequest()) {
                        $this->get('session')->getFlashBag()->add('error', $translator->trans('badge_attribution_error_message', array(), 'platform'));
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
            'users'  => $users,
            'groups' => $groups,
            'form'   => $form->createView()
        );
    }
}