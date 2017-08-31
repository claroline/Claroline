<?php

namespace UJM\ExoBundle\Entity\ItemType;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Entity\Misc\Cell;
use UJM\ExoBundle\Library\Model\PenaltyTrait;

/**
 * A grid question.
 *
 * @ORM\Entity
 * @ORM\Table(name="ujm_question_grid")
 */
class GridQuestion extends AbstractItem
{
    /*
     * The penalty to apply to each wrong answer
     */
    use PenaltyTrait;

    /**
     * @var string
     */
    const SUM_CELL = 'cell';

    /**
     * @var string
     */
    const SUM_COLUMN = 'col';

    /**
     * @var string
     */
    const SUM_ROW = 'row';

    /**
     * List of available cells for the question.
     *
     * @ORM\OneToMany(targetEntity="UJM\ExoBundle\Entity\Misc\Cell", mappedBy="question", cascade={"all"}, orphanRemoval=true)
     *
     * @var ArrayCollection
     */
    private $cells;

    /**
     * Sum sub mode ["cell", "row", "col"].
     *
     * @ORM\Column(type="string")
     *
     * @var string
     */
    private $sumMode = self::SUM_CELL;

    /**
     * Number of rows to draw.
     *
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    private $rows;

    /**
     * Number of columns to draw.
     *
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    private $columns;

    /**
     * Grid border width.
     *
     * @ORM\Column(type="integer")
     *
     * @var string
     */
    private $borderWidth = 1;

    /**
     * Grid border color.
     *
     * @ORM\Column(type="string")
     *
     * @var string
     */
    private $borderColor = '#DDDDDD';

    /**
     * GridQuestion constructor.
     */
    public function __construct()
    {
        $this->cells = new ArrayCollection();
    }

    /**
     * Get cells.
     *
     * @return ArrayCollection
     */
    public function getCells()
    {
        return $this->cells;
    }

    /**
     * Get a cell by its uuid.
     *
     * @param $uuid
     *
     * @return Cell|null
     */
    public function getCell($uuid)
    {
        $found = null;
        foreach ($this->cells as $cell) {
            if ($cell->getUuid() === $uuid) {
                $found = $cell;
                break;
            }
        }

        return $found;
    }

    /**
     * Add cell.
     *
     * @param Cell $cell
     */
    public function addCell(Cell $cell)
    {
        if (!$this->cells->contains($cell)) {
            $cell->SetQuestion($this);
            $this->cells->add($cell);
        }
    }

    /**
     * Remove cell.
     *
     * @param Cell $cell
     */
    public function removeCell(Cell $cell)
    {
        if ($this->cells->contains($cell)) {
            $this->cells->removeElement($cell);
        }
    }

    /**
     * @param string $mode
     */
    public function setSumMode($mode)
    {
        $this->sumMode = $mode;
    }

    /**
     * @return string
     */
    public function getSumMode()
    {
        return $this->sumMode;
    }

    /**
     * Number of rows for the grid.
     *
     * @param number $rows
     */
    public function setRows($rows)
    {
        $this->rows = $rows;
    }

    /**
     * Number of rows for the grid.
     *
     * @return number
     */
    public function getRows()
    {
        return $this->rows;
    }

    /**
     * Number of cols for the grid.
     *
     * @param number $columns
     */
    public function setColumns($columns)
    {
        $this->columns = $columns;
    }

    /**
     * Number of cols for the grid.
     *
     * @return number
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Grid border width.
     *
     * @param number $width
     */
    public function setBorderWidth($width)
    {
        $this->borderWidth = $width;
    }

    /**
     * @return number
     */
    public function getBorderWidth()
    {
        return $this->borderWidth;
    }

    /**
     * Grid border color.
     *
     * @param string $color
     */
    public function setBorderColor($color)
    {
        $this->borderColor = $color;
    }

    /**
     * @return string
     */
    public function getBorderColor()
    {
        return $this->borderColor;
    }

    /**
     * Get styles for the grid.
     *
     * @return array
     */
    public function getGridStyle()
    {
        return ['width' => $this->borderWidth, 'color' => $this->borderColor];
    }
}
