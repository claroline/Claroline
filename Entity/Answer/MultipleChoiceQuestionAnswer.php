<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\SurveyBundle\Entity\Answer;

use Claroline\SurveyBundle\Entity\Answer\QuestionAnswer;
use Claroline\SurveyBundle\Entity\Choice;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_survey_multiple_choice_question_answer")
 */
class MultipleChoiceQuestionAnswer
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\SurveyBundle\Entity\Answer\QuestionAnswer"
     * )
     * @ORM\JoinColumn(name="question_answer_id", nullable=false, onDelete="CASCADE")
     */
    protected $questionAnswer;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\SurveyBundle\Entity\Choice"
     * )
     * @ORM\JoinColumn(name="choice_id", nullable=false, onDelete="CASCADE")
     */
    protected $choice;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getQuestionAnswer()
    {
        return $this->questionAnswer;
    }

    public function setQuestionAnswer(QuestionAnswer $questionAnswer)
    {
        $this->questionAnswer = $questionAnswer;
    }

    public function getChoice()
    {
        return $this->choice;
    }

    public function setChoice(Choice $choice)
    {
        $this->choice = $choice;
    }
}
