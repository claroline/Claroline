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
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\SurveyBundle\Repository\Answer\OpenEndedQuestionAnswerRepository")
 * @ORM\Table(name="claro_survey_open_ended_question_answer")
 */
class OpenEndedQuestionAnswer
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToOne(
     *     targetEntity="Claroline\SurveyBundle\Entity\Answer\QuestionAnswer"
     * )
     * @ORM\JoinColumn(name="question_answer_id", nullable=false, onDelete="CASCADE")
     */
    protected $questionAnswer;

    /**
     * @ORM\Column(name="answer_content", type="text", nullable=true)
     */
    protected $content;

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

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }
}
