<?php

namespace HeVinci\CompetencyBundle\Controller;

use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\Response;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("hasRole('ROLE_USER')")
 */
class WidgetController
{
    /**
     * Displays the content of the learning objectives widget.
     *
     * @EXT\Route("/objectives-widget", name="hevinci_competencies_widget")
     */
    public function objectivesAction()
    {
        return new Response('My learning objectives (widget)');
    }
}
