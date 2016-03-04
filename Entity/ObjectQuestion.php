<?php

namespace UJM\ExoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UJM\ExoBundle\Entity\ObjectQuestion.
 *
 * @ORM\Entity(repositoryClass="UJM\ExoBundle\Repository\ObjectQuestionRepository")
 * @ORM\Table(name="ujm_object_question")
 */
class ObjectQuestion
{

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $resourceNode;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="UJM\ExoBundle\Entity\Question")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $question;

    /**
     * @var int
     *
     * @ORM\Column(name="ordre", type="integer")
     */
    private $ordre;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="text")
     *
     * object or supplementary
     */
    private $type;

    public function __construct(ResourceNode $resourceNode, Question $question)
    {
        $this->resourceNode = $resourceNode;
        $this->question = $question;
    }

    public function setExercise(Exercise $exercise)
    {
        $this->exercise = $exercise;
    }

    public function getExercise()
    {
        return $this->exercise;
    }

    public function setQuestion(Question $question)
    {
        $this->question = $question;
    }

    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * Set ordre.
     *
     * @param int $ordre
     */
    public function setOrdre($ordre)
    {
        $this->ordre = $ordre;
    }

    /**
     * Get ordre.
     *
     * @return int
     */
    public function getOrdre()
    {
        return $this->ordre;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

}
