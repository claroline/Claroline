<?php

namespace Icap\FormulaPluginBundle\Controller;

use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;

class FormulaController
{
    private $profiler;

    /**
     * FormulaController constructor.
     *
     * @DI\InjectParams({
     *     "profiler" = @DI\Inject("profiler")
     * })
     *
     * @param $profiler
     */
    public function __construct($profiler)
    {
        $this->profiler = $profiler;
    }

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
        $this->profiler->disable();

        return [];
    }
}
