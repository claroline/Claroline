<?php

namespace HeVinci\CompetencyBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use HeVinci\CompetencyBundle\Manager\ObjectiveManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("hasRole('ROLE_USER')")
 */
class WidgetController
{
    private $manager;

    /**
     * @DI\InjectParams({
     *     "manager" = @DI\Inject("hevinci.competency.objective_manager")
     * })
     *
     * @param ObjectiveManager $manager
     */
    public function __construct(ObjectiveManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Displays the content of the learning objectives widget.
     *
     * @EXT\Route("/objectives-widget", name="hevinci_competencies_widget")
     * @EXT\ParamConverter("user", options={"authenticatedUser"=true})
     * @EXT\Template
     *
     * @param User $user
     *
     * @return array
     */
    public function objectivesAction(User $user)
    {
        return ['objectives' => $this->manager->loadSubjectObjectives($user)];
    }
}
