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
        return $this->get('icap_portfolio.repository.portfolio');
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

    /**
     * @return \Icap\PortfolioBundle\Form\Handler\PortfolioHandler
     */
    public function getPortfolioFormHandler()
    {
        return $this->get('icap_portfolio.form_handler.portfolio');
    }

    /**
     * @return \Icap\PortfolioBundle\Manager\WidgetsManager
     */
    public function getWidgetsManager()
    {
        return $this->get('icap_portfolio.manager.widgets');
    }
}
