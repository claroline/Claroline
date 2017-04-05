<?php

namespace Claroline\ClacoFormBundle\Entity;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;

/**
 * @ORM\Entity(repositoryClass="Claroline\ClacoFormBundle\Repository\EntryUserRepository")
 * @ORM\Table(
 *     name="claro_clacoformbundle_entry_user",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(
 *             name="clacoform_unique_entry_user",
 *             columns={"entry_id", "user_id"}
 *         )
 *     }
 * )
 */
class EntryUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"api_claco_form"})
     * @SerializedName("id")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\ClacoFormBundle\Entity\Entry",
     *     inversedBy="entryUsers"
     * )
     * @ORM\JoinColumn(name="entry_id", onDelete="CASCADE")
     */
    protected $entry;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", onDelete="CASCADE")
     */
    protected $user;

    /**
     * @ORM\Column(name="shared", type="boolean")
     * @Groups({"api_claco_form"})
     * @SerializedName("shared")
     */
    protected $shared = false;

    /**
     * @ORM\Column(name="notify_edition", type="boolean")
     * @Groups({"api_claco_form"})
     * @SerializedName("notifyEdition")
     */
    protected $notifyEdition = false;

    /**
     * @ORM\Column(name="notify_comment", type="boolean")
     * @Groups({"api_claco_form"})
     * @SerializedName("notifyComment")
     */
    protected $notifyComment = false;

    /**
     * @ORM\Column(name="notify_vote", type="boolean")
     * @Groups({"api_claco_form"})
     * @SerializedName("notifyVote")
     */
    protected $notifyVote = false;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getEntry()
    {
        return $this->entry;
    }

    public function setEntry(Entry $entry)
    {
        $this->entry = $entry;
    }

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
