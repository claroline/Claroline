<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\MessageBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\CoreBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity]
#[ORM\Table(name: 'claro_message')]
#[ORM\Index(name: 'level_idx', columns: ['lvl'])]
#[ORM\Index(name: 'root_idx', columns: ['root'])]
#[Gedmo\Tree(type: 'nested')]
class Message
{
    use Id;
    use Uuid;

    #[ORM\Column]
    private ?string $object = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\JoinColumn(name: 'sender_id', nullable: true, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $user = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Gedmo\Timestampable(on: 'create')]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: Types::INTEGER)]
    #[Gedmo\TreeLeft]
    private ?int $lft = null;

    #[ORM\Column(type: Types::INTEGER)]
    #[Gedmo\TreeLevel]
    private ?int $lvl = null;

    #[ORM\Column(type: Types::INTEGER)]
    #[Gedmo\TreeRight]
    private ?int $rgt = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    #[Gedmo\TreeRoot]
    private ?int $root = null;

    #[ORM\ManyToOne(targetEntity: Message::class, inversedBy: 'children')]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[Gedmo\TreeParent]
    private ?Message $parent = null;

    /**
     * @var Collection<int, Message>
     */
    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'parent')]
    #[ORM\OrderBy(['lft' => 'ASC'])]
    private Collection $children;

    #[ORM\Column(name: 'sender_username')]
    private ?string $senderUsername = 'claroline-connect';

    #[ORM\Column(name: 'receiver_string', type: Types::TEXT)]
    private ?string $to = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $attachments = [];

    public function __construct()
    {
        $this->refreshUuid();

        $this->children = new ArrayCollection();
    }

    public function getObject(): ?string
    {
        return $this->object;
    }

    public function setObject(?string $object): void
    {
        $this->object = $object;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): void
    {
        $this->content = $content;
    }

    public function getSender(): ?User
    {
        return $this->user;
    }

    public function setSender(?User $sender = null): void
    {
        $this->user = $sender;
        $this->senderUsername = $sender ? $sender->getUsername() : 'claroline-connect';
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    /**
     * Sets the message creation date.
     *
     * NOTE : creation date is already handled by the timestamp listener; this
     *        setter exists mainly for testing purposes.
     */
    public function setDate(\DateTimeInterface $date): void
    {
        $this->date = $date;
    }

    public function getParent(): ?Message
    {
        return $this->parent;
    }

    public function setParent(?Message $parent): void
    {
        $this->parent = $parent;
    }

    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function getLft(): ?int
    {
        return $this->lft;
    }

    public function getRgt(): ?int
    {
        return $this->rgt;
    }

    public function getRoot(): ?int
    {
        return $this->root;
    }

    public function getLvl(): ?int
    {
        return $this->lvl;
    }

    public function getTo(): ?string
    {
        return $this->to;
    }

    public function setTo($to): void
    {
        $this->to = $to;
    }

    public function getSenderUsername(): ?string
    {
        return $this->senderUsername;
    }

    public function getReceivers(): array
    {
        $users = [];
        $groups = [];
        $workspaces = [];

        if (!empty($this->to)) {
            $receivers = explode(';', $this->to);
            // split the string of target into different array.
            foreach ($receivers as $receiver) {
                if (!empty($receiver)) {
                    if (str_starts_with($receiver, '{')) {
                        $groups[] = trim($receiver, '{}');
                    } elseif (str_starts_with($receiver, '[')) {
                        $workspaces[] = trim($receiver, '[]');
                    } else {
                        $users[] = $receiver;
                    }
                }
            }
        }

        return [
            'users' => $users,
            'groups' => $groups,
            'workspaces' => $workspaces,
        ];
    }

    public function setReceivers(array $users = [], array $groups = [], array $workspaces = []): void
    {
        $receivers = [];

        if (!empty($users)) {
            $receivers = array_merge($users, $receivers);
        }

        if (!empty($groups)) {
            $receivers = array_merge(array_map(function ($group) {
                return '{'.$group.'}';
            }, $groups), $receivers);
        }

        if (!empty($workspaces)) {
            $receivers = array_merge(array_map(function ($workspace) {
                return '['.$workspace.']';
            }, $workspaces), $receivers);
        }

        $receiversString = implode(';', $receivers);

        $this->setTo($receiversString);
    }

    public function getAttachments(): ?array
    {
        return $this->attachments;
    }

    public function setAttachments(array $attachments): void
    {
        $this->attachments = $attachments;
    }
}
