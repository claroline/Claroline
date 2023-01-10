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
    private $endNavigation = false;

    /**
     * @ORM\Column(name="end_back_type", type="text", nullable=true)
     *
     * @var string
     */
    private $endBackType;

    /**
     * @ORM\Column(name="end_back_label", type="text", nullable=true)
     *
     * @var string
     */
    private $endBackLabel;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode")
     * @ORM\JoinColumn(name="end_back_target_id", nullable=true, onDelete="SET NULL")
     *
     * @var ResourceNode
     */
    private $endBackTarget;

    /**
     * Show buttons on the end page to download WS certificates (participation and success).
     *
     * @ORM\Column(name="show_workspace_certificates", type="boolean")
     *
     * @var bool
     */
    private $showWorkspaceCertificates = false;

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

    public function getEndBackLabel(): ?string
    {
        return $this->endBackLabel;
    }

    public function setEndBackLabel(?string $endBackLabel = null): void
    {
        $this->endBackLabel = $endBackLabel;
    }

    public function getEndBackTarget(): ?ResourceNode
    {
        return $this->endBackTarget;
    }

    public function setEndBackTarget(?ResourceNode $endBackTarget = null): void
    {
        $this->endBackTarget = $endBackTarget;
    }

    public function getShowWorkspaceCertificates(): bool
    {
        return $this->showWorkspaceCertificates;
    }

    public function setShowWorkspaceCertificates(bool $showWorkspaceCertificates): void
    {
        $this->showWorkspaceCertificates = $showWorkspaceCertificates;
    }
}
