<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\SurveyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\SurveyBundle\Repository\SurveyQuestionRelationRepository")
 * @ORM\Table(
 *     name="claro_survey_question_relation",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(
 *             name="survey_unique_survey_question_relation",
 *             columns={"survey_id", "question_id"}
 *         )
 *     }
 * )
 */
class SurveyQuestionRelation
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\SurveyBundle\Entity\Survey",
     *     inversedBy="questionRelations"
     * )
     * @ORM\JoinColumn(name="survey_id", onDelete="CASCADE", nullable=false)
     */
    protected $survey;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\SurveyBundle\Entity\Question"
     * )
     * @ORM\JoinColumn(name="question_id", onDelete="CASCADE", nullable=false)
     */
    protected $question;

    /**
     * @ORM\Column(name="question_order", type="integer")
     */
    protected $questionOrder;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $mandatory = false;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getSurvey()
    {
        return $this->survey;
    }

    public function setSurvey($survey)
    {
        $this->survey = $survey;
    }

    public function getQuestion()
    {
        return $this->question;
    }

    public function setQuestion($question)
    {
        $this->question = $question;
    }

    public function getQuestionOrder()
    {
        return $this->questionOrder;
    }

    public function setQuestionOrder($questionOrder)
    {
        $this->questionOrder = $questionOrder;
    }

    public function getMandatory()
    {
        return $this->mandatory;
    }

    public function setMandatory($mandatory)
    {
        $this->mandatory = $mandatory;
    }

    public function switchMandatory()
    {
        $this->mandatory = !$this->mandatory;
    }
}
