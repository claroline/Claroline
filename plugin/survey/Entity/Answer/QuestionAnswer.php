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

use Claroline\SurveyBundle\Entity\Answer\SurveyAnswer;
use Claroline\SurveyBundle\Entity\Question;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\SurveyBundle\Repository\Answer\QuestionAnswerRepository")
 * @ORM\Table(name="claro_survey_question_answer")
 */
class QuestionAnswer
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\SurveyBundle\Entity\Answer\SurveyAnswer",
     *     inversedBy="questionsAnswers"
     * )
     * @ORM\JoinColumn(name="answer_survey_id", nullable=false, onDelete="CASCADE")
     */
    protected $surveyAnswer;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\SurveyBundle\Entity\Question"
     * )
     * @ORM\JoinColumn(name="question_id", nullable=false, onDelete="CASCADE")
     */
    protected $question;

    /**
     * @ORM\Column(name="answer_comment", type="text", nullable=true)
     */
    protected $comment;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getSurveyAnswer()
    {
        return $this->surveyAnswer;
    }

    public function setSurveyAnswer(SurveyAnswer $surveyAnswer)
    {
        $this->surveyAnswer = $surveyAnswer;
    }

    public function getQuestion()
    {
        return $this->question;
    }

    public function setQuestion(Question $question)
    {
        $this->question = $question;
    }

    public function getComment()
    {
        return $this->comment;
    }

    public function setComment($comment)
    {
        $this->comment = $comment;
    }
}
