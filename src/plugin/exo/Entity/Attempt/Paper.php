<?php

namespace UJM\ExoBundle\Entity\Attempt;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\CoreBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Entity\Exercise;

/**
 * A paper represents a user attempt to a quiz.
 *
 * @ORM\Entity(repositoryClass="UJM\ExoBundle\Repository\PaperRepository")
 * @ORM\Table(name="ujm_paper")
 */
class Paper
{
    use Id;
    use Uuid;

    /**
     * @ORM\Column(name="num_paper", type="integer")
     */
    private $number = 1;

    /**
     * @ORM\Column(type="datetime")
     */
    private $start;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $end = null;

    /**
     * The generated structure (steps and questions) for the attempt.
     *
     * @ORM\Column(name="ordre_question", type="text", nullable=true)
     */
    private $structure;

    /**
     * Used to store temp decoded structure to avoid decoding many times in the same life cycle.
     *
     * @var \stdClass
     */
    private $decodedStructure = null;

    /**
     * @ORM\Column(name="interupt", type="boolean", nullable=true)
     */
    private $interrupted = true;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $score = null;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $total = null;

    /**
     * Anonymize the user information when showing the paper.
     *
     * @var bool
     *
     * @ORM\Column(name="anonymous", type="boolean", nullable=true)
     */
    private $anonymized = false;

    /**
     * A paper is invalidated when the exercise definition has changed.
     *
     * @var bool
     *
     * @ORM\Column(name="invalidated", type="boolean")
     */
    private $invalidated = false;

    /**
     * The user who made the attempt.
     * If this is the attempt for an anonymous user, this property is `null`.
     *
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private $user;

    /**
     * @var Exercise
     *
     * @ORM\ManyToOne(targetEntity="UJM\ExoBundle\Entity\Exercise")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $exercise;

    /**
     * The submitted answers for this attempt.
     *
     * @var Answer[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="UJM\ExoBundle\Entity\Attempt\Answer", mappedBy="paper", cascade={"all"}, orphanRemoval=true)
     */
    private $answers;

    /**
     * Paper constructor.
     */
    public function __construct()
    {
        $this->refreshUuid();

        $this->start = new \DateTime();
        $this->answers = new ArrayCollection();
    }

    /**
     * Sets number.
     *
     * @param int $number
     */
    public function setNumber($number)
    {
        $this->number = $number;
    }

    /**
     * Gets number.
     *
     * @return int
     */
    public function getNumber()
    {
        return $this->number;
    }

    public function setStart(?\DateTimeInterface $start = null): void
    {
        $this->start = $start;
    }

    public function getStart(): ?\DateTimeInterface
    {
        return $this->start;
    }

    public function setEnd(?\DateTimeInterface $end = null): void
    {
        $this->end = $end;
    }

    /**
     * @return \Datetime
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Sets structure.
     *
     * @param string $structure
     */
    public function setStructure($structure)
    {
        $this->structure = $structure;

        // reset stored decoded structure
        $this->decodedStructure = null;
    }

    /**
     * Gets structure.
     *
     * @param bool $decoded
     *
     * @return string|array
     */
    public function getStructure($decoded = false)
    {
        if ($decoded) {
            return $this->getDecodedStructure();
        }

        return $this->structure;
    }

    /**
     * @param bool $interrupted
     */
    public function setInterrupted($interrupted)
    {
        $this->interrupted = $interrupted;
    }

    /**
     * @return bool
     */
    public function isInterrupted()
    {
        return $this->interrupted;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user = null)
    {
        $this->user = $user;
    }

    /**
     * @return Exercise
     */
    public function getExercise()
    {
        return $this->exercise;
    }

    public function setExercise(Exercise $exercise)
    {
        $this->exercise = $exercise;
    }

    /**
     * @param float $score
     */
    public function setScore($score)
    {
        $this->score = $score;
    }

    /**
     * @return float
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * @param float $total
     */
    public function setTotal($total)
    {
        $this->total = $total;
    }

    /**
     * @return float
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Set anonymized.
     *
     * @param bool $anonymized
     */
    public function setAnonymized($anonymized)
    {
        $this->anonymized = $anonymized;
    }

    /**
     * Is anonymized ?
     *
     * @return bool
     */
    public function isAnonymized()
    {
        return $this->anonymized;
    }

    /**
     * Set invalidated.
     *
     * @param $invalidated
     */
    public function setInvalidated($invalidated)
    {
        $this->invalidated = $invalidated;
    }

    /**
     * Is invalidated ?
     *
     * @return bool
     */
    public function isInvalidated()
    {
        return $this->invalidated;
    }

    /**
     * Gets answers.
     *
     * @return Answer[]|ArrayCollection
     */
    public function getAnswers()
    {
        return $this->answers;
    }

    /**
     * Gets a question in the paper structure.
     *
     * @param $questionUuid
     *
     * @return array
     */
    public function getQuestion($questionUuid)
    {
        $question = null;

        $decoded = $this->getDecodedStructure();
        foreach ($decoded['steps'] as $step) {
            foreach ($step['items'] as $item) {
                if ($item['id'] === $questionUuid) {
                    $question = $item;
                    break 2;
                }
            }
        }

        return $question;
    }

    /**
     * Get all the hints available in the paper structure.
     *
     * @return array
     */
    public function getHints()
    {
        $hints = [];

        $decoded = $this->getDecodedStructure();
        foreach ($decoded['steps'] as $step) {
            foreach ($step['items'] as $item) {
                if (1 === preg_match('#^application\/x\.[^/]+\+json$#', $item['type'])) {
                    foreach ($item['hints'] as $hint) {
                        $hints[$hint['id']] = $hint;
                    }
                }
            }
        }

        return $hints;
    }

    /**
     * Gets the answer to a question if any exist.
     *
     * @param string $questionUuid
     *
     * @return Answer
     */
    public function getAnswer($questionUuid)
    {
        $found = null;
        foreach ($this->answers as $answer) {
            if ($answer->getQuestionId() === $questionUuid) {
                $found = $answer;
                break;
            }
        }

        return $found;
    }

    /**
     * Adds an answer.
     */
    public function addAnswer(Answer $answer)
    {
        if (!$this->answers->contains($answer)) {
            $this->answers->add($answer);
            $answer->setPaper($this);
        }
    }

    /**
     * Removes an answer.
     */
    public function removeAnswer(Answer $answer)
    {
        if ($this->answers->contains($answer)) {
            $this->answers->removeElement($answer);
        }
    }

    private function getDecodedStructure()
    {
        if (empty($this->decodedStructure)) {
            $this->decodeStructure();
        }

        return $this->decodedStructure;
    }

    private function decodeStructure()
    {
        $this->decodedStructure = json_decode($this->structure, true);
    }
}
