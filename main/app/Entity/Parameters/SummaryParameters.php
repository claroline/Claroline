<?php

namespace Claroline\AppBundle\Entity\Parameters;

use Doctrine\ORM\Mapping as ORM;

trait SummaryParameters
{
    /**
     * Show summary.
     *
     * @ORM\Column(name="show_summary", type="boolean", options={"default" = 1})
     *
     * @var bool
     */
    private $showSummary = true;

    /**
     * Open summary.
     *
     * @ORM\Column(name="open_summary", type="boolean", options={"default" = 1})
     *
     * @var bool
     */
    private $openSummary = true;

    /**
     * Set show summary.
     *
     * @param bool $showSummary
     */
    public function setShowSummary($showSummary)
    {
        $this->showSummary = $showSummary;
    }

    /**
     * Is summary shown ?
     *
     * @return bool
     */
    public function getShowSummary()
    {
        return $this->showSummary;
    }

    /**
     * Set open summary.
     *
     * @param bool $openSummary
     */
    public function setOpenSummary($openSummary)
    {
        $this->openSummary = $openSummary;
    }

    /**
     * Is summary opened ?
     *
     * @return bool
     */
    public function getOpenSummary()
    {
        return $this->openSummary;
    }
}
