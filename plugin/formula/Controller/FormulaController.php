<?php

namespace Icap\FormulaPluginBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;

class FormulaController
{
    /**
     * @EXT\Route(
     *     "/index",
     *     name="icap_formula_plugin_index",
     *     options={"expose"=true}
     * )
     * @EXT\Template("IcapFormulaPluginBundle:formula:index.html.twig")
     *
     * @return array
     */
    public function indexAction()
    {
        return [];
    }
}
