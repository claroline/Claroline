<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Form\BadgeType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Library\Event\DisplayWidgetEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Claroline\CoreBundle\Library\Event\DisplayToolEvent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Controller of the badges.
 */
class BadgeController extends Controller
{
    /**
     * @Route("/admin/badges", name="claro_admin_badges")
     *
     * @Template()
     */
    public function listAction()
    {
        if (!$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            throw new \AccessDeniedException();
        }

        /** @var \Claroline\CoreBundle\Repository\BadgeRepository $badgeRepository */
        $badgeRepository = $this->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Badge\Badge');

        $badges = $badgeRepository->findAllOrderedByName();

        return array(
            'badges' => $badges
        );
    }
    /**
     * @Route("/admin/badges/add", name="claro_admin_badges_add")
     *
     * @Template()
     */
    public function addAction()
    {
        if (!$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            throw new \AccessDeniedException();
        }

        $form = $this->createForm(new BadgeType());

        return array(
            'form' => $form->createView()
        );
    }
}