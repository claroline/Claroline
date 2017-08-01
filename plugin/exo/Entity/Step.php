<?php

namespace UJM\ExoBundle\Entity;

use Claroline\CoreBundle\Entity\Model\UuidTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Entity\Item\Item;
use UJM\ExoBundle\Library\Model\AttemptParametersTrait;
use UJM\ExoBundle\Library\Model\OrderTrait;

/**
 * A step represents a group of items (questions or content) inside an exercise.
 * It also have its specific attempt parameters.
 *
 * @ORM\Entity(repositoryClass="UJM\ExoBundle\Repository\StepRepository")
 * @ORM\Table(name="ujm_step")
 */
class Step
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    use UuidTrait;

    use OrderTrait;

    use AttemptParametersTrait;

    /**
     * @var int
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="Exercise", inversedBy="steps")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $exercise;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="StepItem", mappedBy="step", cascade={"all"}, orphanRemoval=true)
     * @ORM\OrderBy({"order" = "ASC"})
     */
    private $stepQuestions;

    /**
     * Step constructor.
     */
    public function __construct()
    {
        $this->refreshUuid();
        $this->stepQuestions = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title.
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description.
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param Exercise $exercise
     */
    public function setExercise(Exercise $exercise)
    {
        $this->exercise = $exercise;
    }

    /**
     * @return Exercise
     */
    public function getExercise()
    {
        return $this->exercise;
    }

    /**
     * @return ArrayCollection
     */
    public function getStepQuestions()
    {
        return $this->stepQuestions;
    }

    /**
     * @param StepItem $stepQuestion
     */
    public function addStepQuestion(StepItem $stepQuestion)
    {
        if (!$this->stepQuestions->contains($stepQuestion)) {
            $this->stepQuestions->add($stepQuestion);
        }
    }

    /**
     * @param StepItem $stepQuestion
     */
    public function removeStepQuestion(StepItem $stepQuestion)
    {
        if ($this->stepQuestions->contains($stepQuestion)) {
            $this->stepQuestions->removeElement($stepQuestion);
        }
    }

    /**
     * Gets a question by its uuid.
     *
     * @param string $uuid
     *
     * @return Item|null
     */
    public function getQuestion($uuid)
    {
        $found = null;
        foreach ($this->stepQuestions as $stepQuestion) {
            if ($stepQuestion->getQuestion()->getUuid() === $uuid) {
                $found = $stepQuestion->getQuestion();
                break;
            }
        }

        return $found;
    }

    /**
     * Shortcut to get the list of questions of the step.
     *
     * @return Item[]
     */
    public function getQuestions()
    {
        return array_map(function (StepItem $stepQuestion) {
            return $stepQuestion->getQuestion();
        }, $this->stepQuestions->toArray());
    }

    /**
     * Shortcut to add Items to Step.
     * Avoids the need to manually initialize a StepItem object to hold the relation.
     *
     * @param Item $question - the question to add to the step
     */
    public function addQuestion(Item $question)
    {
        $stepQuestions = $this->stepQuestions->toArray();
        foreach ($stepQuestions as $stepQuestion) {
            /** @var StepItem $stepQuestion */
            if ($stepQuestion->getQuestion() === $question) {
                return; // The question is already linked to the Step
            }
        }

        // Create a new StepItem to attach the question to the step
        $stepQuestion = new StepItem();
        $stepQuestion->setOrder($this->stepQuestions->count());
        $stepQuestion->setStep($this);
        $stepQuestion->setQuestion($question);
    }
}
