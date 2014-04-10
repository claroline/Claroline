<?php

namespace Icap\PortfolioBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;

class Controller extends BaseController
{
    /**
     * @return \Icap\PortfolioBundle\Repository\PortfolioRepository
     */
    public function getPortfolioRepository()
    {
        return $this->get('icap.portfolio.portfolio_repository');
    }

    /**
     * @return \Symfony\Bundle\FrameworkBundle\Translation\Translator
     */
    public function getTranslator()
    {
        return $this->get('translator');
    }

    /**
     * @return \Symfony\Bundle\FrameworkBundle\Translation\Translator
     */
    public function getSessionFlashbag()
    {
        return $this->get('session')->getFlashBag();
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    public function getEntityManager()
    {
        return $this->getDoctrine()->getManager();
    }
}
