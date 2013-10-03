<?php

namespace Claroline\CoreBundle\Controller\Badge;

use Claroline\CoreBundle\Entity\Badge\UserBadge;
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
    public function viewAction(UserBadge $userBadge)
    {
        /** @var \Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler $platformConfigHandler */
        $platformConfigHandler = $this->get('claroline.config.platform_config_handler');

        $badge = $userBadge->getBadge();
        $badge->setLocale($platformConfigHandler->getParameter('locale_language'));

        return array(
            'badge' => $badge
        );
    }
}
