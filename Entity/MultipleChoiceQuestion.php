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

use Claroline\SurveyBundle\Entity\AbstractTypedQuestion;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\SurveyBundle\Repository\MultipleChoiceQuestionRepository")
 * @ORM\Table(name="claro_survey_multiple_choice_question")
 */
class MultipleChoiceQuestion extends AbstractTypedQuestion
{
    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\SurveyBundle\Entity\Choice",
     *     mappedBy="choiceQuestion"
     * )
     */
    protected $choices;

    /**
     * @ORM\Column(name="allow_multiple_response", type="boolean", nullable=true)
     */
    protected $allowMultipleResponse;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->choices = new ArrayCollection();
        $this->allowMultipleResponse = false;
    }

    public function getChoices()
    {
        return $this->choices;
    }

    public function getAllowMultipleResponse()
    {
        return $this->allowMultipleResponse;
    }

    public function setAllowMultipleResponse($allowMultipleResponse)
    {
        $this->allowMultipleResponse = $allowMultipleResponse;
    }
}
