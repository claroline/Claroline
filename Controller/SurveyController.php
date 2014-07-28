<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\SurveyBundle\Controller;

use Claroline\SurveyBundle\Entity\Survey;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SurveyController extends Controller
{
    /**
     * @EXT\Route("/{survey}", name="claro_survey_index")
     * @EXT\Template
     *
     * @param Survey $survey
     * @return array
     */
    public function indexAction(Survey $survey)
    {
        return array('_resource' => $survey);
    }
}
