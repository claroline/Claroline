<?php

namespace UJM\ExoBundle\Entity\Attempt;

use Claroline\CoreBundle\Entity\Model\UuidTrait;
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
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    use UuidTrait;

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
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
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
     * @var ArrayCollection
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
     * Gets id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
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

    /**
     * Sets start date.
     *
     * @param \DateTime $start
     */
    public function setStart(\DateTime $start)
    {
        $this->start = $start;
    }

    /**
     * Gets start date.
     *
     * @return \Datetime
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @param \Datetime $end
     */
    public function setEnd($end)
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
    }

    /**
     * Gets structure.
     *
     * @return string
     */
    public function getStructure()
    {
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

    /**
     * @param User $user
     */
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

    /**
     * @param Exercise $exercise
     */
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
     * @return ArrayCollection
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
     * @return \stdClass
     */
    public function getQuestion($questionUuid)
    {
        $question = null;

        if (empty($this->decodedStructure)) {
            $this->decodeStructure();
        }

        foreach ($this->decodedStructure->steps as $step) {
            foreach ($step->items as $item) {
                if ($item->id === $questionUuid) {
                    $question = $item;
                    break 2;
                }
            }
        }

        return $question;
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
     *
     * @param Answer $answer
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
     *
     * @param Answer $answer
     */
    public function removeAnswer(Answer $answer)
    {
        if ($this->answers->contains($answer)) {
            $this->answers->removeElement($answer);
        }
    }

    private function decodeStructure()
    {
        $this->decodedStructure = json_decode($this->structure);
    }
}
