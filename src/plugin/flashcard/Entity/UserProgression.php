<?php

namespace Claroline\FlashcardBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * UserProgression
 * Represents the progression of a User on a Card.
 *
 * @ORM\Table(name="claro_flashcard_progression")
 *
 * @ORM\Entity(repositoryClass="Claroline\FlashcardBundle\Repository\UserProgressionRepository")
 */
class UserProgression
{
    use Id;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\FlashcardBundle\Entity\Flashcard")
     *
     * @ORM\JoinColumn(name="flashcard_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @var Flashcard
     */
    private $flashcard;

    /**
     * User for which we track the progression.
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     *
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @var User
     */
    private $user;

    /**
     * @ORM\Column(name="is_successful", type="boolean")
     */
    private bool $isSuccessful;

    public function __construct()
    {
        $this->isSuccessful = false;
    }

    /**
     * Get Card.
     *
     * @return Flashcard
     */
    public function getFlashcard()
    {
        return $this->flashcard;
    }

    /**
     * Set Card.
     *
     * @return UserProgression
     */
    public function setFlashcard(Flashcard $card)
    {
        $this->flashcard = $card;

        return $this;
    }

    /**
     * Get User.
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * Set User.
     *
     * @return UserProgression
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get successful status.
     */
    public function isSuccessful(): bool
    {
        return $this->isSuccessful;
    }

    /**
     * Set successful status.
     *
     * @return UserProgression
     */
    public function setIsSuccessful(bool $successful)
    {
        $this->isSuccessful = $successful;

        return $this;
    }
}
