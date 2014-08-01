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

use Claroline\SurveyBundle\Entity\AbstractQuestion;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_survey_multiple_choice_question")
 */
class Question extends AbstractQuestion
{
    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\SurveyBundle\Entity\MultipleChoice\Choice",
     *     mappedBy="question"
     * )
     */
    private $choices;

    public function __construct()
    {
        $this->choices = new ArrayCollection();
    }

    public function addChoice(Choice $choice)
    {
        $this->choices->add($choice);
    }

    public function getChoices()
    {
        return $this->choices;
    }
}
