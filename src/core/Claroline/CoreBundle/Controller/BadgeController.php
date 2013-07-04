<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Entity\Badge\Badge;
use Claroline\CoreBundle\Entity\Badge\BadgeTranslation;
use Claroline\CoreBundle\Form\BadgeType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Library\Event\DisplayWidgetEvent;
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

        $badges = $badgeRepository->findAllOrderedByName($platformConfigHandler->getParameter('locale_language'));

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

        $locales = array('fr', 'en');
        foreach($locales as $locale)
        {
            $translation = new BadgeTranslation();
            $translation->setLocale($locale);
            $badge->addTranslation($translation);
        }

        $form = $this->createForm(new BadgeType(), $badge);

        if('POST' === $request->getMethod()) {
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
                    echo "<pre>";
                    var_dump($exception->getMessage());
                    echo "</pre>" . PHP_EOL;
                    die("FFFFFUUUUUCCCCCKKKKK" . PHP_EOL);
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
     * @Route("/edit/{slug}", name="claro_admin_badges_edit")
     *
     * @Template()
     */
    public function editAction(Request $request, Badge $badge)
    {
        if (!$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            throw new \AccessDeniedException();
        }

        $form = $this->createForm(new BadgeType(), $badge);

        if('POST' === $request->getMethod()) {
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
     * @Route("/delete/{slug}", name="claro_admin_badges_delete")
     * @ParamConverter("badge", class="ClarolineCoreBundle:Badge\Badge")
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
}