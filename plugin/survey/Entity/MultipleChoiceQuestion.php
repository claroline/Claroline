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
     * @ORM\Column(type="boolean")
     */
    protected $horizontal;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->choices = new ArrayCollection();
        $this->horizontal = false;
    }

    public function getChoices()
    {
        return $this->choices;
    }

    public function getHorizontal()
    {
        return $this->horizontal;
    }

    public function setHorizontal($horizontal)
    {
        $this->horizontal = $horizontal;
    }
}
