<?php

namespace UJM\ExoBundle\Entity\ItemType;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Entity\Misc\Cell;
use UJM\ExoBundle\Library\Model\PenaltyTrait;

/**
 * A grid question.
 */
#[ORM\Table(name: 'ujm_question_grid')]
#[ORM\Entity]
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
     *
     * @var ArrayCollection
     */
    #[ORM\OneToMany(targetEntity: \UJM\ExoBundle\Entity\Misc\Cell::class, mappedBy: 'question', cascade: ['all'], orphanRemoval: true)]
    private $cells;

    /**
     * Sum sub mode ["cell", "row", "col"].
     *
     *
     * @var string
     */
    #[ORM\Column(type: 'string')]
    private $sumMode = self::SUM_CELL;

    /**
     * Number of rows to draw.
     *
     *
     * @var int
     */
    #[ORM\Column(name: 'grid_rows', type: 'integer')]
    private $rows;

    /**
     * Number of columns to draw.
     *
     *
     * @var int
     */
    #[ORM\Column(name: 'grid_columns', type: 'integer')]
    private $columns;

    /**
     * Grid border width.
     *
     *
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    private $borderWidth = 1;

    /**
     * Grid border color.
     *
     *
     * @var string
     */
    #[ORM\Column(type: 'string')]
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
     * @return Cell[]|ArrayCollection
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
