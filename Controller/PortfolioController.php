<?php

namespace Icap\PortfolioBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use Icap\PortfolioBundle\Entity\Portfolio;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use JMS\DiExtraBundle\Annotation\Inject;

class PortfolioController extends Controller
{
    /**
     * @Route("/{page}", name="icap_portfolio_list", requirements={"page" = "\d+"}, defaults={"page" = 1})
     * @ParamConverter("loggedUser", options={"authenticatedUser" = true})
     * @Template()
     */
    public function listAction(User $loggedUser, $page)
    {
        $query = $this->getPortfolioRepository()->findByUser($loggedUser, false);
        $pager = $this->get('claroline.pager.pager_factory')->createPager($query, $page, 10);

        return array(
            'pager' => $pager
        );
    }

    /**
     * @Route("/delete/{id}", name="icap_portfolio_delete", requirements={"page" = "\d+"}, defaults={"page" = 1})
     * @ParamConverter("loggedUser", options={"authenticatedUser" = true})
     * @Template()
     */
    public function deleteAction(User $loggedUser, Portfolio $portfolio)
    {
        if ($loggedUser !== $portfolio->getUser()) {
            throw $this->createNotFoundException("Unkown user for this portfolio.");
        }

        try {
            $this->getEntityManager()->remove($portfolio);
            $this->getEntityManager()->flush();

            $this->getSessionFlashbag()->add('success', $this->getTranslator()->trans('icap_portfolio_delete_success', array(), 'icap_portfolio'));
        } catch (\Exception $exception) {
            $this->getSessionFlashbag()->add('error', $this->getTranslator()->trans('icap_portfolio_delete_error', array(), 'icap_portfolio'));
        }

        return $this->redirect($this->generateUrl('icap_portfolio_list'));
    }
}
 