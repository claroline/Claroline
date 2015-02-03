<?php

namespace HeVinci\CompetencyBundle\Controller;

use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("canOpenAdminTool('competencies')")
 */
class CompetencyController
{
    /**
     * Displays the index of the competency tool, i.e. the list
     * of competency frameworks.
     *
     * @EXT\Route("/frameworks", name="hevinci_competencies_index")
     * @EXT\Template
     *
     * @return array
     */
    public function frameworksAction()
    {
        return [];
    }
}
