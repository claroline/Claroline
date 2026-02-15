<?php

namespace UJM\ExoBundle\Entity;

use Claroline\AppBundle\Entity\Display\Order;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use UJM\ExoBundle\Entity\Item\Item;
use UJM\ExoBundle\Library\Model\AttemptParametersTrait;

/**
 * A step represents a group of items (questions or content) inside an exercise.
 * It also has its specific attempt parameters.
 */
#[ORM\Table(name: 'ujm_step')]
#[ORM\Entity]
class Step
{
    use Id;
    use AttemptParametersTrait;
    use Order;
    use Uuid;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Exercise::class, inversedBy: 'steps')]
    private ?Exercise $exercise = null;

    /**
     * @var Collection<int, StepItem>
     */
    #[ORM\OneToMany(targetEntity: StepItem::class, mappedBy: 'step', cascade: ['all'], orphanRemoval: true)]
    #[ORM\OrderBy(['order' => 'ASC'])]
    private Collection $stepQuestions;

    #[ORM\Column(length: 128)]
    #[Gedmo\Slug(fields: ['title'], unique: false)]
    private ?string $slug = null;

    public function __construct()
    {
        $this->refreshUuid();
        $this->stepQuestions = new ArrayCollection();
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setExercise(Exercise $exercise): void
    {
        $this->exercise = $exercise;
    }

    public function getExercise(): ?Exercise
    {
        return $this->exercise;
    }

    /**
     * @return ArrayCollection|StepItem[]
     */
    public function getStepQuestions(): Collection
    {
        return $this->stepQuestions;
    }

    public function addStepQuestion(StepItem $stepQuestion): void
    {
        if (!$this->stepQuestions->contains($stepQuestion)) {
            $this->stepQuestions->add($stepQuestion);
        }
    }

    public function removeStepQuestion(StepItem $stepQuestion): void
    {
        if ($this->stepQuestions->contains($stepQuestion)) {
            $this->stepQuestions->removeElement($stepQuestion);
        }
    }

    public function getQuestion(string $uuid): ?Item
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
    public function getQuestions(): array
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
    public function addQuestion(Item $question): StepItem
    {
        $stepQuestions = $this->stepQuestions->toArray();
        foreach ($stepQuestions as $stepQuestion) {
            /** @var StepItem $stepQuestion */
            if ($stepQuestion->getQuestion() === $question) {
                return $stepQuestion; // The question is already linked to the Step
            }
        }

        // Create a new StepItem to attach the question to the step
        $stepQuestion = new StepItem();
        $stepQuestion->setOrder($this->stepQuestions->count());
        $stepQuestion->setStep($this);
        $stepQuestion->setQuestion($question);

        return $stepQuestion;
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }
}
