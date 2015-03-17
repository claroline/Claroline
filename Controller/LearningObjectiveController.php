<?php

namespace HeVinci\CompetencyBundle\Controller;

use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("canOpenAdminTool('learning-objectives')")
 * @EXT\Route("/objectives", requirements={"id"="\d+"}, options={"expose"=true})
 * @EXT\Method("GET")
 */
class LearningObjectiveController
{
    /**
     * Displays the index of the learning objectives tool, i.e.
     * the list of learning objectives.
     *
     * @EXT\Route("/", name="hevinci_objectives_index")
     * @EXT\Template
     *
     * @return array
     */
    public function objectivesAction()
    {
        return [];
    }

    /**
     * Displays the index of the learning objectives tool, i.e.
     * the list of learning objectives.
     *
     * @EXT\Route("/users", name="hevinci_objectives_users")
     * @EXT\Template
     *
     * @return array
     */
    public function usersAction()
    {
        return [];
    }

    /**
     * Displays the index of the learning objectives tool, i.e.
     * the list of learning objectives.
     *
     * @EXT\Route("/groups", name="hevinci_objectives_groups")
     * @EXT\Template
     *
     * @return array
     */
    public function groupsAction()
    {
        return [];
    }
}
