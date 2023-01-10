<?php

namespace Claroline\CoreBundle\Entity\Resource;

use Doctrine\ORM\Mapping as ORM;

trait HasEndPage
{
    /**
     * Show an end page when the user has finished the quiz.
     *
     * @ORM\Column(name="show_end_page", type="boolean")
     *
     * @var bool
     */
    private $showEndPage = false;

    /**
     * A message to display at the end of the quiz.
     *
     * @ORM\Column(name="end_message", type="text", nullable=true)
     *
     * @var string
     */
    private $endMessage = '';

    /**
     * Show navigation buttons on the end page.
     *
     * @ORM\Column(name="end_navigation", type="boolean")
     *
     * @var bool
     */
    private $endNavigation = true;

    /**
     * @ORM\Column(name="end_back_type", type="text", nullable=true)
     *
     * @var string
     */
    private $endBackType;

    /**
     * @ORM\Column(name="end_back_title", type="text", nullable=true)
     *
     * @var string
     */
    private $endBackTitle;

    /**
     * @ORM\Column(name="end_back_target", type="text", nullable=true)
     *
     * @var string
     */
    private $endBackTarget;

    public function getShowEndPage(): bool
    {
        return $this->showEndPage;
    }

    public function setShowEndPage(bool $showEndPage): void
    {
        $this->showEndPage = $showEndPage;
    }

    public function getEndMessage(): ?string
    {
        return $this->endMessage;
    }

    public function setEndMessage(?string $endMessage = null): void
    {
        $this->endMessage = $endMessage;
    }

    public function hasEndNavigation(): bool
    {
        return $this->endNavigation;
    }

    public function setEndNavigation(bool $endNavigation): void
    {
        $this->endNavigation = $endNavigation;
    }

    public function getEndBackType(): ?string
    {
        return $this->endBackType;
    }

    public function setEndBackType(?string $endBackType = null): void
    {
        $this->endBackType = $endBackType;
    }

    public function getEndBackTitle(): ?string
    {
        return $this->endBackTitle;
    }

    public function setEndBackTitle(?string $endBackTitle = null): void
    {
        $this->endBackTitle = $endBackTitle;
    }

    public function getEndBackTarget(): ?string
    {
        return $this->endBackTarget;
    }

    public function setEndBackTarget(?string $endBackTarget = null): void
    {
        $this->endBackTarget = $endBackTarget;
    }
}
