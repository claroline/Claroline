<?php

namespace UJM\ExoBundle\Entity\Misc;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use UJM\ExoBundle\Library\Attempt\AnswerPartInterface;
use UJM\ExoBundle\Library\Model\FeedbackTrait;
use UJM\ExoBundle\Library\Model\ScoreTrait;

/**
 * CellChoice.
 *
 * @ORM\Entity()
 * @ORM\Table(name="ujm_cell_choice")
 */
class CellChoice implements AnswerPartInterface
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    use ScoreTrait;

    use FeedbackTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="response", type="string", length=255)
     */
    private $text;

    /**
     * @var bool
     *
     * @ORM\Column(name="caseSensitive", type="boolean", nullable=true)
     */
    private $caseSensitive;

    /**
     * @var Cell
     *
     * @ORM\ManyToOne(targetEntity="UJM\ExoBundle\Entity\Misc\Cell", inversedBy="choices")
     * @ORM\JoinColumn(name="cell_id", referencedColumnName="id")
     */
    private $cell;

    /**
     * @var bool
     * @ORM\Column(name="expected", type="boolean", nullable=true)
     */
    private $expected;

    /**
     * CellChoice constructor.
     */
    public function __construct()
    {
        $this->uuid = Uuid::uuid4()->toString();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get text.
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set text.
     *
     * @param string $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * Is the Cell choice case sensitive ?
     *
     * @return bool
     */
    public function isCaseSensitive()
    {
        return $this->caseSensitive;
    }

    /**
     * Set caseSensitive.
     *
     * @param bool $caseSensitive
     */
    public function setCaseSensitive($caseSensitive)
    {
        $this->caseSensitive = $caseSensitive;
    }

    /**
     * Is the cell choice expected ?
     * Usefull only in SumMode row / col and Global score.
     *
     * @return bool
     */
    public function isExpected()
    {
        return $this->expected;
    }

    /**
     * Set expected
     * Usefull only in SumMode row / col and Global score.
     *
     * @param bool $expected
     */
    public function setExpected($expected)
    {
        $this->expected = $expected;
    }

    /**
     * @return Cell
     */
    public function getCell()
    {
        return $this->cell;
    }

    /**
     * @param Cell $cell
     */
    public function setCell(Cell $cell)
    {
        $this->cell = $cell;
    }
}
