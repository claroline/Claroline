<?php

namespace Claroline\FlashcardBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\CoreBundle\Entity\Resource\ResourceEvaluation;
use Doctrine\ORM\Mapping as ORM;

/**
 * CardDrawnProgression
 * Represents the progression of a Card drawn.
 *
 * @ORM\Table(name="claro_flashcard_drawn_progression")
 *
 * @ORM\Entity()
 */
class CardDrawnProgression
{
    use Id;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\FlashcardBundle\Entity\Flashcard")
     *
     * @ORM\JoinColumn(name="flashcard_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private Flashcard $flashcard;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceEvaluation")
     *
     * @ORM\JoinColumn(name="resource_evaluation_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private ResourceEvaluation $resourceEvaluation;

    /**
     * @ORM\Column(name="success_count", type="integer")
     */
    private int $successCount;

    public function __construct()
    {
        $this->successCount = -1;
    }

    /**
     * Get Card.
     */
    public function getFlashcard(): Flashcard
    {
        return $this->flashcard;
    }

    /**
     * Set Card.
     *
     * @return CardDrawnProgression
     */
    public function setFlashcard(Flashcard $card): static
    {
        $this->flashcard = $card;

        return $this;
    }

    /**
     * Get ResourceEvaluation.
     */
    public function getResourceEvaluation(): ResourceEvaluation
    {
        return $this->resourceEvaluation;
    }

    /**
     * Set ResourceEvaluation.
     *
     * @return CardDrawnProgression
     */
    public function setResourceEvaluation(ResourceEvaluation $resourceEvaluation): static
    {
        $this->resourceEvaluation = $resourceEvaluation;

        return $this;
    }

    /**
     * Get successful status.
     */
    public function isSuccessful(): bool
    {
        return $this->successCount > 0;
    }

    /**
     * Get successful status.
     */
    public function isAnswered(): bool
    {
        return $this->successCount > -1;
    }

    /**
     * Get SuccessCount.
     */
    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    /**
     * Set SuccessCount.
     *
     * @return CardDrawnProgression
     */
    public function setSuccessCount(int $successCount): static
    {
        $this->successCount = $successCount;

        return $this;
    }
}
