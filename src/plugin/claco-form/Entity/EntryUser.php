<?php

namespace Claroline\ClacoFormBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_clacoformbundle_entry_user')]
#[ORM\UniqueConstraint(name: 'clacoform_unique_entry_user', columns: ['entry_id', 'user_id'])]
#[ORM\Entity(repositoryClass: \Claroline\ClacoFormBundle\Repository\EntryUserRepository::class)]
class EntryUser
{
    use Id;
    use Uuid;

    /**
     *
     * @var Entry
     */
    #[ORM\JoinColumn(name: 'entry_id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: \Claroline\ClacoFormBundle\Entity\Entry::class, inversedBy: 'entryUsers')]
    protected $entry;

    /**
     *
     * @var User
     */
    #[ORM\JoinColumn(name: 'user_id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: \Claroline\CoreBundle\Entity\User::class)]
    protected $user;

    #[ORM\Column(name: 'shared', type: 'boolean')]
    protected $shared = false;

    #[ORM\Column(name: 'notify_edition', type: 'boolean')]
    protected $notifyEdition = false;

    #[ORM\Column(name: 'notify_comment', type: 'boolean')]
    protected $notifyComment = false;

    #[ORM\Column(name: 'notify_vote', type: 'boolean')]
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
