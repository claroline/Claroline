<?php

namespace Claroline\ClacoFormBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Claroline\ClacoFormBundle\Repository\EntryUserRepository;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_clacoformbundle_entry_user')]
#[ORM\UniqueConstraint(name: 'clacoform_unique_entry_user', columns: ['entry_id', 'user_id'])]
#[ORM\Entity(repositoryClass: EntryUserRepository::class)]
class EntryUser
{
    use Id;
    use Uuid;

    /**
     *
     * @var Entry
     */
    #[ORM\JoinColumn(name: 'entry_id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Entry::class, inversedBy: 'entryUsers')]
    protected ?Entry $entry = null;

    /**
     *
     * @var User
     */
    #[ORM\JoinColumn(name: 'user_id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: User::class)]
    protected ?User $user = null;

    #[ORM\Column(name: 'shared', type: Types::BOOLEAN)]
    protected $shared = false;

    #[ORM\Column(name: 'notify_edition', type: Types::BOOLEAN)]
    protected $notifyEdition = false;

    #[ORM\Column(name: 'notify_comment', type: Types::BOOLEAN)]
    protected $notifyComment = false;

    #[ORM\Column(name: 'notify_vote', type: Types::BOOLEAN)]
    protected $notifyVote = false;

    public function __construct()
    {
        $this->refreshUuid();
    }

    /**
     * @return Entry
     */
    public function getEntry()
    {
        return $this->entry;
    }

    public function setEntry(Entry $entry)
    {
        $this->entry = $entry;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function isShared()
    {
        return $this->shared;
    }

    public function setShared($shared)
    {
        $this->shared = $shared;
    }

    public function getNotifyEdition()
    {
        return $this->notifyEdition;
    }

    public function setNotifyEdition($notifyEdition)
    {
        $this->notifyEdition = $notifyEdition;
    }

    public function getNotifyComment()
    {
        return $this->notifyComment;
    }

    public function setNotifyComment($notifyComment)
    {
        $this->notifyComment = $notifyComment;
    }

    public function getNotifyVote()
    {
        return $this->notifyVote;
    }

    public function setNotifyVote($notifyVote)
    {
        $this->notifyVote = $notifyVote;
    }
}
