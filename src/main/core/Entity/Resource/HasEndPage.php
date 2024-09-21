<?php

namespace Claroline\CoreBundle\Entity\Resource;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait HasEndPage
{
    /**
     * Show an end page when the user has finished the quiz.
     *
     *
     * @var bool
     */
    #[ORM\Column(name: 'show_end_page', type: Types::BOOLEAN)]
    private $showEndPage = false;

    /**
     * A message to display at the end of the quiz.
     *
     *
     * @var string
     */
    #[ORM\Column(name: 'end_message', type: Types::TEXT, nullable: true)]
    private $endMessage = '';

    /**
     * Show navigation buttons on the end page.
     *
     *
     * @var bool
     */
    #[ORM\Column(name: 'end_navigation', type: Types::BOOLEAN)]
    private $endNavigation = false;

    /**
     * @var string
     */
    #[ORM\Column(name: 'end_back_type', type: Types::TEXT, nullable: true)]
    private $endBackType;

    /**
     * @var string
     */
    #[ORM\Column(name: 'end_back_label', type: Types::TEXT, nullable: true)]
    private $endBackLabel;

    /**
     *
     * @var ResourceNode
     */
    #[ORM\JoinColumn(name: 'end_back_target_id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: ResourceNode::class)]
    private ?ResourceNode $endBackTarget = null;

    /**
     * Show buttons on the end page to download WS certificates (participation and success).
     *
     *
     * @var bool
     */
    #[ORM\Column(name: 'show_workspace_certificates', type: Types::BOOLEAN)]
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
