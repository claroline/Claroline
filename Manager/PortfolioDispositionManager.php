<?php

namespace Icap\PortfolioBundle\Manager;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Icap\PortfolioBundle\Entity\Portfolio;
use Icap\PortfolioBundle\Entity\Widget\AbstractWidget;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactory;

/**
 * @DI\Service("icap_portfolio.manager.portfolio_disposition")
 */
class PortfolioDispositionManager
{
    /**
     * @param integer $disposition
     *
     * @return array
     */
    public function getColumnsForDisposition($disposition)
    {
        $cols = array();
        switch($disposition) {
            case 1:
                $cols = [1, 2];
                break;
            case 2:
                $cols = [1, 2, 3];
                break;
        }

        return $cols;
    }
}
 