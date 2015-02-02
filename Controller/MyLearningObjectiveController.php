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
class MyLearningObjectiveController
{
    /**
     * Displays the index of the learner version of the learning
     * objectives tool, i.e the list of his learning objectives.
     *
     * @EXT\Route("/my-objectives", name="hevinci_my_objectives_index")
     */
    public function objectivesAction()
    {
        return new Response('My learning objectives list');
    }
}
