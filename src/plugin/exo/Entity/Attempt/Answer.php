<?php

namespace UJM\ExoBundle\Entity\Attempt;

use UJM\ExoBundle\Repository\AnswerRepository;
use Doctrine\DBAL\Types\Types;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Entity\Item\Item;

/**
 * An answer represents a user answer to a question.
 */
#[ORM\Table(name: 'ujm_response')]
#[ORM\Entity(repositoryClass: AnswerRepository::class)]
class Answer
{
    use Id;
    use Uuid;

    /**
     * @var string
     */
    #[ORM\Column]
    private $ip;

    /**
     * The score obtained for this question.
     *
     * @var float
     */
    #[ORM\Column(name: 'mark', type: Types::FLOAT, nullable: true)]
    private $score;

    /**
     * A custom feedback sets by a creator.
     *
     * @var string
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private $feedback = '';

    /**
     * @var int
     */
    #[ORM\Column(name: 'nb_tries', type: Types::INTEGER)]
    private $tries = 0;

    /**
     * The answer data formatted in string for DB storage.
     *
     * @var string
     */
    #[ORM\Column(name: 'response', type: Types::TEXT, nullable: true)]
    private $data;

    /**
     * The list of hints used to answer the question.
     *
     *
     * @var array
     */
    #[ORM\Column(name: 'used_hints', type: 'simple_array', nullable: true)]
    private $usedHints = [];

    /**
     * @var Paper
     */
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Paper::class, inversedBy: 'answers')]
    private $paper;

    /**
     * The id of the question that is answered.
     *
     * @var Item
     */
    #[ORM\Column(name: 'question_id', type: Types::STRING, length: 36)]
    private $questionId;

    public function __construct()
    {
        $this->refreshUuid();
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
        if (!$this->feedback) {
            return '';
        }

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
