<?php

namespace UJM\ExoBundle\Entity\Misc;

use Doctrine\DBAL\Types\Types;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Library\Attempt\AnswerPartInterface;
use UJM\ExoBundle\Library\Model\FeedbackTrait;
use UJM\ExoBundle\Library\Model\ScoreTrait;

/**
 * CellChoice.
 */
#[ORM\Table(name: 'ujm_cell_choice')]
#[ORM\Entity]
class CellChoice implements AnswerPartInterface
{
    use Id;
    use FeedbackTrait;
    use ScoreTrait;
    use Uuid;

    /**
     * @var string
     */
    #[ORM\Column(name: 'response', type: Types::STRING, length: 255)]
    private $text;

    /**
     * @var bool
     */
    #[ORM\Column(name: 'caseSensitive', type: Types::BOOLEAN, nullable: true)]
    private $caseSensitive;

    /**
     * @var Cell
     */
    #[ORM\JoinColumn(name: 'cell_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: Cell::class, inversedBy: 'choices')]
    private $cell;

    /**
     * @var bool
     */
    #[ORM\Column(name: 'expected', type: Types::BOOLEAN, nullable: true)]
    private $expected;

    /**
     * CellChoice constructor.
     */
    public function __construct()
    {
        $this->refreshUuid();
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

    public function setCell(Cell $cell)
    {
        $this->cell = $cell;
    }
}
