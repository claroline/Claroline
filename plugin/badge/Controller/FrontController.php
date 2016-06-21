<?php

namespace Icap\BadgeBundle\Controller;

use Icap\BadgeBundle\Entity\Badge;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use JMS\SecurityExtraBundle\Annotation as SEC;

/**
 * Controller of the badges.
 *
 * @Route("/badge")
 */
class FrontController extends Controller
{
    /**
     * @Route("/{slug}", name="icap_badge_view_badge")
     * @ParamConverter("badge", converter="badge_converter", options={"check_deleted" = false})
     *
     * @SEC\Secure(roles="ROLE_USER")
     *
     * @Template()
     */
    public function viewAction(Badge $badge)
    {
        return array(
            'badge' => $badge,
        );
    }
}
