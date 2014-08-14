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

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_survey_resource")
 */
class Survey extends AbstractResource
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Claroline\SurveyBundle\Entity\SurveyQuestionRelation",
     *     mappedBy="survey"
     * )
     */
    protected $questionRelations;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $published = false;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $closed = false;

    /**
     * @ORM\Column(name="has_public_result", type="boolean")
     */
    protected $hasPublicResult = false;

    /**
     * @ORM\Column(name="allow_answer_edition", type="boolean")
     */
    protected $allowAnswerEdition = false;

    /**
     * @ORM\Column(name="start_date", type="datetime", nullable=true)
     */
    protected $startDate;

    /**
     * @ORM\Column(name="end_date", type="datetime", nullable=true)
     */
    protected $endDate;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Claroline\SurveyBundle\Entity\Answer\SurveyAnswer",
     *     mappedBy="survey"
     * )
     */
    protected $answers;

    public function __construct()
    {
        $this->questionRelations = new ArrayCollection();
        $this->answers = new ArrayCollection();
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return boolean
     */
    public function isClosed()
    {
        return $this->closed;
    }

    /**
     * @param boolean $closed
     */
    public function setClosed($closed)
    {
        $this->closed = $closed;
    }

    /**
     * @return boolean
     */
    public function isPublished()
    {
        return $this->published;
    }

    /**
     * @param boolean $published
     */
    public function setPublished($published)
    {
        $this->published = $published;
    }

    /**
     * @return boolean
     */
    public function getHasPublicResult()
    {
        return $this->hasPublicResult;
    }

    /**
     * @param boolean $hasPublicResult
     */
    public function setHasPublicResult($hasPublicResult)
    {
        $this->hasPublicResult = $hasPublicResult;
    }

    /**
     * @return boolean
     */
    public function getAllowAnswerEdition()
    {
        return $this->allowAnswerEdition;
    }

    /**
     * @param boolean $allowAnswerEdition
     */
    public function setAllowAnswerEdition($allowAnswerEdition)
    {
        $this->allowAnswerEdition = $allowAnswerEdition;
    }

    /**
     * @return datetime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param datetime $startDate
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    }

    /**
     * @return datetime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param datetime $endDate
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    }

    public function getQuestionRelations()
    {
        return $this->questionRelations;
    }

    public function setQuestionRelations(ArrayCollection $questionRelations)
    {
        $this->questionRelations = $questionRelations;
    }

    public function getAnswers()
    {
        return $this->answers;
    }

    public function setAnswers(ArrayCollection $answers)
    {
        $this->answers = $answers;
    }
}
