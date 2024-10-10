<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AudioPlayerBundle\Entity\Resource;

use Claroline\AppBundle\Entity\Display\Color;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_audio_resource_section')]
#[ORM\Entity]
class Section
{
    use Id;
    use Uuid;
    use Color;

    #[ORM\JoinColumn(name: 'node_id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: ResourceNode::class)]
    private ?ResourceNode $resourceNode = null;

    #[ORM\JoinColumn(name: 'user_id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $user = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(name: 'section_start', type: Types::FLOAT, nullable: false)]
    private ?float $start = null;

    #[ORM\Column(name: 'section_end', type: Types::FLOAT, nullable: false)]
    private ?float $end = null;

    #[ORM\Column(name: 'section_type')]
    private ?string $type = null;

    #[ORM\Column(name: 'show_transcript', type: Types::BOOLEAN)]
    private bool $showTranscript = false;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $transcript = null;

    #[ORM\Column(name: 'comments_allowed', type: Types::BOOLEAN)]
    private bool $commentsAllowed = false;

    #[ORM\Column(name: 'show_help', type: Types::BOOLEAN)]
    private bool $showHelp = false;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $help;

    #[ORM\Column(name: 'show_audio', type: Types::BOOLEAN)]
    private bool $showAudio = false;

    #[ORM\Column(name: 'audio_url', type: Types::STRING, nullable: true)]
    private ?string $audioUrl;

    #[ORM\Column(name: 'audio_description', type: Types::STRING, nullable: true)]
    private ?string $audioDescription;

    /**
     * @var Collection<int, SectionComment>
     */
    #[ORM\OneToMany(targetEntity: SectionComment::class, mappedBy: 'section')]
    #[ORM\OrderBy(['creationDate' => 'DESC'])]
    protected Collection $comments;

    public function __construct()
    {
        $this->refreshUuid();
        $this->comments = new ArrayCollection();
    }

    public function getResourceNode(): ?ResourceNode
    {
        return $this->resourceNode;
    }

    public function setResourceNode(ResourceNode $resourceNode): void
    {
        $this->resourceNode = $resourceNode;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user = null): void
    {
        $this->user = $user;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getStart(): ?float
    {
        return $this->start;
    }

    public function setStart(float $start): void
    {
        $this->start = $start;
    }

    public function getEnd(): ?float
    {
        return $this->end;
    }

    public function setEnd(float $end): void
    {
        $this->end = $end;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getShowTranscript(): bool
    {
        return $this->showTranscript;
    }

    public function setShowTranscript(bool $showTranscript): void
    {
        $this->showTranscript = $showTranscript;
    }

    public function getTranscript(): ?string
    {
        return $this->transcript;
    }

    public function setTranscript(?string $transcript): void
    {
        $this->transcript = $transcript;
    }

    public function isCommentsAllowed(): bool
    {
        return $this->commentsAllowed;
    }

    public function setCommentsAllowed(bool $commentsAllowed): void
    {
        $this->commentsAllowed = $commentsAllowed;
    }

    public function getShowHelp(): bool
    {
        return $this->showHelp;
    }

    public function setShowHelp(bool $showHelp): void
    {
        $this->showHelp = $showHelp;
    }

    public function getHelp(): ?string
    {
        return $this->help;
    }

    public function setHelp(?string $help): void
    {
        $this->help = $help;
    }

    public function getShowAudio(): bool
    {
        return $this->showAudio;
    }

    public function setShowAudio(bool $showAudio): void
    {
        $this->showAudio = $showAudio;
    }

    public function getAudioUrl(): ?string
    {
        return $this->audioUrl;
    }

    public function setAudioUrl(string $audioUrl): void
    {
        $this->audioUrl = $audioUrl;
    }

    public function getAudioDescription(): ?string
    {
        return $this->audioDescription;
    }

    public function setAudioDescription(?string $audioDescription): void
    {
        $this->audioDescription = $audioDescription;
    }

    public function getComments(): Collection
    {
        return $this->comments;
    }
}
