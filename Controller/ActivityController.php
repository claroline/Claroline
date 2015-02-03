<?php

namespace HeVinci\CompetencyBundle\Controller;

use Claroline\CoreBundle\Entity\Resource\Activity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;

class ActivityController
{
    /**
     * Displays the competency management page of an activity.
     *
     * @EXT\Route("/activity/{id}", name="hevinci_activity_competencies_index")
     * @EXT\ParamConverter(
     *     "activity",
     *     class="ClarolineCoreBundle:Resource\Activity",
     *     options={"id" = "id", "strictId" = true}
     * )
     * @EXT\Template
     *
     * @param Activity $activity
     * @return array
     */
    public function competenciesAction(Activity $activity)
    {
        return ['_resource' => $activity];
    }
}
