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

use Claroline\SurveyBundle\Entity\MultipleChoiceQuestion;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\SurveyBundle\Repository\ChoiceRepository")
 * @ORM\Table(name="claro_survey_choice")
 */
class Choice
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="text")
     */
    protected $content;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\SurveyBundle\Entity\MultipleChoiceQuestion",
     *     inversedBy="choices"
     * )
     * @ORM\JoinColumn(name="choice_question_id", nullable=false, onDelete="CASCADE")
     */
    protected $choiceQuestion;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $other = false;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return MultipleChoiceQuestion
     */
    public function getChoiceQuestion()
    {
        return $this->choiceQuestion;
    }

    /**
     * @param MultipleChoiceQuestion $choiceQuestion
     */
    public function setChoiceQuestion(MultipleChoiceQuestion $choiceQuestion)
    {
        $this->choiceQuestion = $choiceQuestion;
    }

    /**
     * @return boolean
     */
    public function isOther()
    {
        return $this->other;
    }

    /**
     * @param boolean $other
     */
    public function setOther($other)
    {
        $this->other = $other;
    }
}
