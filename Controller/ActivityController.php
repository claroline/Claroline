<?php

namespace HeVinci\CompetencyBundle\Controller;

use Claroline\CoreBundle\Entity\Resource\Activity;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;

/**
 * @EXT\ParamConverter(
 *     "activity",
 *     class="ClarolineCoreBundle:Resource\Activity",
 *     options={"id" = "id", "strictId" = true}
 * )
 * @SEC\SecureParam(name="activity", permissions="OPEN")
 */
class ActivityController
{
    /**
     * Displays the competency management page of an activity.
     *
     * @EXT\Route("/activity/{id}", name="hevinci_activity_competencies_index")
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
