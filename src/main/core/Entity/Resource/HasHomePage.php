<?php

namespace Claroline\CoreBundle\Entity\Resource;

use Doctrine\ORM\Mapping as ORM;

trait HasHomePage
{
    /**
     * Show overview to users or directly start the quiz.
     *
     * @ORM\Column(name="show_overview", type="boolean")
     *
     * @var bool
     */
    private $showOverview = true;

    /**
     * @ORM\Column(name="description", type="text", nullable=true)
     *
     * @var string
     */
    private $overviewMessage = '';

    public function getShowOverview(): bool
    {
        return $this->showOverview;
    }

    public function setShowOverview(bool $showOverview): void
    {
        $this->showOverview = $showOverview;
    }

    public function getOverviewMessage(): ?string
    {
        return $this->overviewMessage;
    }

    public function setOverviewMessage(?string $overviewMessage = null): void
    {
        $this->overviewMessage = $overviewMessage;
    }
}
