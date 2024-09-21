<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\BigBlueButtonBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_bigbluebuttonbundle_bbb')]
#[ORM\Entity(repositoryClass: \Claroline\BigBlueButtonBundle\Repository\BBBRepository::class)]
class BBB extends AbstractResource
{
    #[ORM\Column(name: 'welcome_message', type: 'text', nullable: true)]
    private ?string $welcomeMessage = null;

    #[ORM\Column(name: 'end_message', type: 'text', nullable: true)]
    private ?string $endMessage = null;

    #[ORM\Column(name: 'new_tab', type: 'boolean')]
    private bool $newTab = true;

    #[ORM\Column(name: 'moderator_required', type: 'boolean')]
    private bool $moderatorRequired = true;

    #[ORM\Column(name: 'record', type: 'boolean')]
    private bool $record = false;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $ratio = 56.25;

    #[ORM\Column(name: 'activated', type: 'boolean')]
    private bool $activated = true;

    /**
     * Forces the server on which the room will be running.
     */
    #[ORM\Column(nullable: true)]
    private ?string $server = null;

    /**
     * Defines on which server the room is currently running.
     */
    #[ORM\Column(nullable: true)]
    private ?string $runningOn = null;

    /**
     * Allows users to change their username before entering the room.
     */
    #[ORM\Column(type: 'boolean')]
    private bool $customUsernames = false;

    #[ORM\OneToMany(targetEntity: \Claroline\BigBlueButtonBundle\Entity\Recording::class, mappedBy: 'meeting', orphanRemoval: true)]
    #[ORM\OrderBy(['startTime' => 'DESC'])]
    private Collection $recordings;

    public function __construct()
    {
        parent::__construct();

        $this->recordings = new ArrayCollection();
    }

    public function getWelcomeMessage(): ?string
    {
        return $this->welcomeMessage;
    }

    public function setWelcomeMessage(?string $welcomeMessage = null): void
    {
        $this->welcomeMessage = $welcomeMessage;
    }

    public function getEndMessage(): ?string
    {
        return $this->endMessage;
    }

    public function setEndMessage(?string $endMessage = null): void
    {
        $this->endMessage = $endMessage;
    }

    public function isNewTab(): bool
    {
        return $this->newTab;
    }

    public function setNewTab(bool $newTab): void
    {
        $this->newTab = $newTab;
    }

    public function isModeratorRequired(): bool
    {
        return $this->moderatorRequired;
    }

    public function setModeratorRequired($moderatorRequired): void
    {
        $this->moderatorRequired = $moderatorRequired;
    }

    public function isRecord(): bool
    {
        return $this->record;
    }

    public function setRecord($record): void
    {
        $this->record = $record;
    }

    public function getRatio(): ?float
    {
        return $this->ratio;
    }

    public function setRatio(float $ratio = null): void
    {
        $this->ratio = $ratio;
    }

    public function isActivated(): bool
    {
        return $this->activated;
    }

    public function setActivated(bool $activated): void
    {
        $this->activated = $activated;
    }

    public function getServer(): ?string
    {
        return $this->server;
    }

    public function setServer(string $server = null): void
    {
        $this->server = $server;
    }

    public function getRunningOn(): ?string
    {
        return $this->runningOn;
    }

    public function setRunningOn(string $server = null): void
    {
        $this->runningOn = $server;
    }

    public function hasCustomUsernames(): bool
    {
        return $this->customUsernames;
    }

    public function setCustomUsernames(bool $customUsernames): void
    {
        $this->customUsernames = $customUsernames;
    }

    /** @return Recording[] */
    public function getRecordings(): Collection
    {
        return $this->recordings;
    }

    public function addRecording(Recording $recording): void
    {
        if (!$this->recordings->contains($recording)) {
            $this->recordings->add($recording);
            $recording->setMeeting($this);
        }
    }

    public function removeRecording(Recording $recording): void
    {
        if ($this->recordings->contains($recording)) {
            $this->recordings->removeElement($recording);
        }
    }

    public function getLastRecording(): ?Recording
    {
        if (!empty($this->recordings)) {
            return $this->recordings[0];
        }

        return null;
    }
}
