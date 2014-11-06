<?php

namespace Icap\PortfolioBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use Icap\PortfolioBundle\Entity\Portfolio;
use Icap\PortfolioBundle\Entity\Widget\TitleWidget;
use Icap\PortfolioBundle\Event\Log\PortfolioViewEvent;
use Icap\PortfolioBundle\Manager\PortfolioManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use JMS\DiExtraBundle\Annotation\Inject;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/portfolio/{portfolioSlug}")
 */
class CommentController extends Controller
{
    /**
     * @Route("/comments", name="icap_portfolio_comments_list")
     *
     * @ParamConverter("loggedUser", options={"authenticatedUser" = true})
     * @Template()
     */
    public function listAction(User $loggedUser, $portfolioSlug)
    {
        $this->checkPortfolioToolAccess();

        /** @var \Icap\PortfolioBundle\Entity\Widget\TitleWidget $titleWidget */
        $titleWidget = $this->getDoctrine()->getRepository('IcapPortfolioBundle:Widget\TitleWidget')->findOneBySlug($portfolioSlug);

        if (null === $titleWidget) {
            throw $this->createNotFoundException();
        }

        $portfolio = $titleWidget->getPortfolio();

        return array(
            'portfolio'   => $portfolio,
            'titlewidget' => $titleWidget
        );
    }
}
 