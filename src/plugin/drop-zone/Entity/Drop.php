<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\DropZoneBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\CoreBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\DropZoneBundle\Repository\DropRepository")
 * @ORM\Table(
 *     name="claro_dropzonebundle_drop",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(
 *             name="dropzone_drop_unique_dropzone_team",
 *             columns={"dropzone_id", "team_id"}
 *         )
 *     })
 */
class Drop
{
    use Id;
    use Uuid;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\DropZoneBundle\Entity\Dropzone")
     * @ORM\JoinColumn(name="dropzone_id", nullable=false, onDelete="CASCADE")
     *
     * @var Dropzone
     */
    protected $dropzone;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", nullable=true, onDelete="SET NULL")
     *
     * @var User
     */
    protected $user;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\DropZoneBundle\Entity\Document",
     *     mappedBy="drop"
     * )
     *
     * @var Document
     */
    protected $documents;

    /**
     * @ORM\Column(name="drop_date", type="datetime", nullable=true)
     *
     * @var \DateTime
     */
    protected $dropDate;

    /**
     * @ORM\Column(name="score", type="float", nullable=true)
     *
     * @var float
     */
    protected $score;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     *
     * @var bool
     */
    protected $finished = false;

    /**
     * Indicate if the drop was close automaticaly (when time is up by the dropzone option $autoCloseDropsAtDropEndDate).
     *
     * @ORM\Column(name="auto_closed_drop", type="boolean", nullable=false)
     *
     * @var bool
     */
    protected $autoClosedDrop = false;

    /**
     * Used to flag that a copy have been unlocked ( admin made a correction that unlocked the copy:
     * the copy doesn't wait anymore the expected number of correction.
     *
     * @ORM\Column(name="unlocked_drop", type="boolean", nullable=false)
     *
     * @var bool
     */
    protected $unlockedDrop = false;

    /**
     * Used to flag that a user have been unlocked ( admin made a correction that unlocked the copy:
     * the drop author will not need anymore to do the expected number of correction to see his copy.).
     *
     * @ORM\Column(name="unlocked_user", type="boolean", nullable=false)
     *
     * @var bool
     */
    protected $unlockedUser = false;

    /**
     * @ORM\Column(name="team_id", type="integer", nullable=true)
     *
     * @var int
     */
    protected $teamId;

    /**
     * @ORM\Column(name="team_uuid", nullable=true)
     *
     * @var string
     */
    protected $teamUuid;

    /**
     * @ORM\Column(name="team_name", nullable=true)
     *
     * @var string
     */
    protected $teamName;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\DropZoneBundle\Entity\Correction",
     *     mappedBy="drop"
     * )
     *
     * @var ArrayCollection|Correction[]
     */
    protected $corrections;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\User"
     * )
     * @ORM\JoinTable(name="claro_dropzonebundle_drop_users")
     *
     * @var User
     */
    protected $users;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\DropZoneBundle\Entity\Revision",
     *     mappedBy="drop"
     * )
     * @ORM\OrderBy({"creationDate" = "DESC"})
     */
    protected $revisions;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\DropZoneBundle\Entity\DropComment",
     *     mappedBy="drop"
     * )
     */
    protected $comments;

    /**
     * Drop constructor.
     */
    public function __construct()
    {
        $this->refreshUuid();

        $this->documents = new ArrayCollection();
        $this->corrections = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->revisions = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    /**
     * @return Dropzone
     */
    public function getDropzone()
    {
        return $this->dropzone;
    }

    public function setDropzone(Dropzone $dropzone)
    {
        $this->dropzone = $dropzone;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user = null)
    {
        $this->user = $user;
    }

    /**
     * @return Document[]
     */
    public function getDocuments()
    {
        return $this->documents->toArray();
    }

    public function addDocument(Document $document)
    {
        if (!$this->documents->contains($document)) {
            $this->documents->add($document);
        }
    }

    public function removeDocument(Document $document)
    {
        if ($this->documents->contains($document)) {
            $this->documents->removeElement($document);
        }
    }

    public function emptyDocuments()
    {
        $this->documents->clear();
    }

    /**
     * @return \DateTime
     */
    public function getDropDate()
    {
        return $this->dropDate;
    }

    public function setDropDate(\DateTime $dropDate = null)
    {
        $this->dropDate = $dropDate;
    }

    /**
     * @return float
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * @param float $score
     */
    public function setScore($score)
    {
        $this->score = $score;
    }

    /**
     * @return bool
     */
    public function isFinished()
    {
        return $this->finished;
    }

    /**
     * @param bool $finished
     */
    public function setFinished($finished)
    {
        $this->finished = $finished;
    }

    /**
     * @return bool
     */
    public function getAutoClosedDrop()
    {
        return $this->autoClosedDrop;
    }

    /**
     * @param bool $autoClosedDrop
     */
    public function setAutoClosedDrop($autoClosedDrop)
    {
        $this->autoClosedDrop = $autoClosedDrop;
    }

    /**
     * @return bool
     */
    public function isUnlockedDrop()
    {
        return $this->unlockedDrop;
    }

    /**
     * @param bool $unlockedDrop
     */
    public function setUnlockedDrop($unlockedDrop)
    {
        $this->unlockedDrop = $unlockedDrop;
    }

    /**
     * @return bool
     */
    public function isUnlockedUser()
    {
        return $this->unlockedUser;
    }

    /**
     * @param bool $unlockedUser
     */
    public function setUnlockedUser($unlockedUser)
    {
        $this->unlockedUser = $unlockedUser;
    }

    /**
     * @return int
     */
    public function getTeamId()
    {
        return $this->teamId;
    }

    /**
     * @param int $teamId
     */
    public function setTeamId($teamId)
    {
        $this->teamId = $teamId;
    }

    /**
     * @return string
     */
    public function getTeamUuid()
    {
        return $this->teamUuid;
    }

    /**
     * @param string $teamUuid
     */
    public function setTeamUuid($teamUuid)
    {
        $this->teamUuid = $teamUuid;
    }

    /**
     * @return string
     */
    public function getTeamName()
    {
        return $this->teamName;
    }

    /**
     * @param string $teamName
     */
    public function setTeamName($teamName)
    {
        $this->teamName = $teamName;
    }

    /**
     * @return Correction[]
     */
    public function getCorrections()
    {
        return $this->corrections->toArray();
    }

    public function addCorrection(Correction $correction)
    {
        if (!$this->corrections->contains($correction)) {
            $this->corrections->add($correction);
        }
    }

    public function removeCorrection(Correction $correction)
    {
        if ($this->corrections->contains($correction)) {
            $this->corrections->removeElement($correction);
        }
    }

    public function emptyCorrections()
    {
        $this->corrections->clear();
    }

    /**
     * @return User[]
     */
    public function getUsers()
    {
        return $this->users->toArray();
    }

    /**
     * @return bool
     */
    public function hasUser(User $user)
    {
        return $this->users->contains($user);
    }

    public function addUser(User $user)
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
        }
    }

    public function removeUser(User $user)
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
        }
    }

    public function emptyUsers()
    {
        $this->users->clear();
    }

    /**
     * @return ArrayCollection
     */
    public function getRevisions()
    {
        return $this->revisions;
    }

    /**
     * @return ArrayCollection
     */
    public function getComments()
    {
        return $this->comments;
    }
}
