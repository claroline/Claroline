<?php

namespace Icap\FormulaPluginBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\Routing\Annotation\Route;

class FormulaController
{
    /**
     * @Route("/formula", name="icap_formula_plugin_index", options={"expose"=true})
     * @EXT\Template("@IcapFormulaPlugin/formula/index.html.twig")
     *
     * @return array
     */
    public function indexAction()
    {
        return [];
    }
}
