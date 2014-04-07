<?php

namespace Icap\NotificationBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/portfolio")
 */
class PortfolioController extends Controller
{
    /**
     * @Route("/", name="icap_portfolio_list", requirements={"blogId" = "\d+", "page" = "\d+"}, defaults={"page" = 1})
     * @ParamConverter("loggedUser", options={"authenticatedUser" = true})
     * @Template()
     */
    public function listAction(User $user)
    {
        return array(
        );
    }
}
 