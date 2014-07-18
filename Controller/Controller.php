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
     * @return \Icap\PortfolioBundle\Repository\Widget\WidgetTypeRepository
     */
    public function getPortfolioWidgetTypeRepository()
    {
        return $this->get('icap_portfolio.repository.portfolio_widget_type');
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
     * @return \Icap\PortfolioBundle\Manager\PortfolioManager
     */
    public function getPortfolioManager()
    {
        return $this->get('icap_portfolio.manager.portfolio');
    }

    /**
     * @return \Icap\PortfolioBundle\Manager\PortfolioDispositionManager
     */
    public function getPortfolioDispositionManager()
    {
        return $this->get('icap_portfolio.manager.portfolio_disposition');
    }

    /**
     * @return \Icap\PortfolioBundle\Manager\WidgetsManager
     */
    public function getWidgetsManager()
    {
        return $this->get('icap_portfolio.manager.widgets');
    }
}
