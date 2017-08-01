<?php

namespace UJM\ExoBundle\Entity\Attempt;

use Claroline\CoreBundle\Entity\Model\UuidTrait;
use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Entity\Item\Item;

/**
 * An answer represents a user answer to a question.
 *
 * @ORM\Entity(repositoryClass="UJM\ExoBundle\Repository\AnswerRepository")
 * @ORM\Table(name="ujm_response")
 */
class Answer
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
     * @var string
     *
     * @ORM\Column
     */
    private $ip;

    /**
     * The score obtained for this question.
     *
     * @var float
     *
     * @ORM\Column(name="mark", type="float", nullable=true)
     */
    private $score;

    /**
     * A custom feedback sets by a creator.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $feedback;

    /**
     * @var int
     *
     * @ORM\Column(name="nb_tries", type="integer")
     */
    private $tries = 0;

    /**
     * The answer data formatted in string for DB storage.
     *
     * @var string
     *
     * @ORM\Column(name="response", type="text", nullable=true)
     */
    private $data;

    /**
     * The list of hints used to answer the question.
     *
     * @ORM\Column(name="used_hints", type="simple_array", nullable=true)
     *
     * @var array
     */
    private $usedHints = [];

    /**
     * @var Paper
     *
     * @ORM\ManyToOne(targetEntity="UJM\ExoBundle\Entity\Attempt\Paper", inversedBy="answers")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $paper;

    /**
     * The id of the question that is answered.
     *
     * @var Item
     *
     * @ORM\Column(name="question_id", type="string", length=36)
     */
    private $questionId;

    public function __construct()
    {
        $this->refreshUuid();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $ip
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
    }

    /**
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Sets score.
     *
     * @param float $score
     */
    public function setScore($score)
    {
        $this->score = $score;
    }

    /**
     * Gets score.
     *
     * @return float
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * Sets feedback.
     *
     * @param $feedback
     */
    public function setFeedback($feedback)
    {
        $this->feedback = $feedback;
    }

    /**
     * Gets feedback.
     *
     * @return string
     */
    public function getFeedback()
    {
        return $this->feedback;
    }

    /**
     * Gets number or tries.
     *
     * @param int $tries
     */
    public function setTries($tries)
    {
        $this->tries = $tries;
    }

    /**
     * Sets number of tries.
     *
     * @return int
     */
    public function getTries()
    {
        return $this->tries;
    }

    /**
     * Sets data.
     *
     * @param string $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * Gets data.
     *
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Gets used hints.
     *
     * @return array
     */
    public function getUsedHints()
    {
        return $this->usedHints;
    }

    /**
     * Adds an Hint.
     *
     * @param string $hintId
     */
    public function addUsedHint($hintId)
    {
        if (!in_array($hintId, $this->usedHints)) {
            $this->usedHints[] = $hintId;
        }
    }

    /**
     * Removes an Hint.
     *
     * @param string $hintId
     */
    public function removeUsedHint($hintId)
    {
        $pos = array_search($hintId, $this->usedHints);
        if (false !== $pos) {
            array_splice($this->usedHints, $pos, 1);
        }
    }

    /**
     * @param Paper $paper
     */
    public function setPaper(Paper $paper)
    {
        $this->paper = $paper;
    }

    /**
     * @return Paper
     */
    public function getPaper()
    {
        return $this->paper;
    }

    /**
     * @return string
     */
    public function getQuestionId()
    {
        return $this->questionId;
    }

    /**
     * @param string $questionId
     */
    public function setQuestionId($questionId)
    {
        $this->questionId = $questionId;
    }
}
