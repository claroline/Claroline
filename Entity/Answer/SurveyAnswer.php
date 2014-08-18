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

use Claroline\CoreBundle\Entity\User;
use Claroline\SurveyBundle\Entity\Survey;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\SurveyBundle\Repository\Answer\SurveyAnswerRepository")
 * @ORM\Table(name="claro_survey_answer")
 */
class SurveyAnswer
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
     *     inversedBy="answers"
     * )
     * @ORM\JoinColumn(name="survey_id", nullable=false, onDelete="CASCADE")
     */
    protected $survey;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User"
     * )
     * @ORM\JoinColumn(name="user_id", nullable=false, onDelete="CASCADE")
     */
    protected $user;

    /**
     * @ORM\Column(name="answer_date", type="datetime", nullable=false)
     */
    protected $answerDate;

    /**
     * @ORM\Column(name="nb_answers", type="integer", nullable=false)
     */
    protected $nbAnswers;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\SurveyBundle\Entity\Answer\QuestionAnswer",
     *     mappedBy="surveyAnswer"
     * )
     */
    protected $questionsAnswers;

    public function __construct()
    {
        $this->questionsAnswers = new ArrayCollection();
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getSurvey()
    {
        return $this->survey;
    }

    public function setSurvey(Survey $survey)
    {
        $this->survey = $survey;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function getAnswerDate()
    {
        return $this->answerDate;
    }

    public function setAnswerDate($answerDate)
    {
        $this->answerDate = $answerDate;
    }

    public function getNbAnswers()
    {
        return $this->nbAnswers;
    }

    public function setNbAnswers($nbAnswers)
    {
        $this->nbAnswers = $nbAnswers;
    }

    public function incrementNbAnswers()
    {
        $this->nbAnswers++;
    }

    public function getQuestionsAnswers()
    {
        return $this->questionsAnswers;
    }

    public function setQuestionsAnswers(ArrayCollection $questionsAnswers)
    {
        $this->questionsAnswers = $questionsAnswers;
    }
}
