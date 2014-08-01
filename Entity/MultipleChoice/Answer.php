<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\SurveyBundle\Entity\MultipleChoice;

use Claroline\SurveyBundle\Entity\AbstractAnswer;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_survey_multiple_choice_answer")
 */
class Answer extends AbstractAnswer
{
    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\SurveyBundle\Entity\MultipleChoice\Choice"
     * )
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $choice;

    /**
     * @param Choice $choice
     */
    public function setChoice(Choice $choice)
    {
        $this->choice = $choice;
    }

    /**
     * @return Choice
     */
    public function getChoice()
    {
        return $this->choice;
    }
}
