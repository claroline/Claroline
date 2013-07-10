<?php

namespace Claroline\BadgeBundle\Controller;

use Claroline\BadgeBundle\Entity\Badge;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Controller of the badges.
 *
 * @Route("/badge")
 */
class FrontController extends Controller
{
    /**
     * @Route("/view/{id}", name="claro_view_badge")
     *
     * @Template()
     */
    public function viewAction(Badge $badge)
    {
        return array(
            'badge' => $badge
        );
    }
}