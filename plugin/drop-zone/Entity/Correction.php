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

use Claroline\CoreBundle\Entity\Model\UuidTrait;
use Claroline\CoreBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\DropZoneBundle\Repository\CorrectionRepository")
 * @ORM\Table(name="claro_dropzonebundle_correction")
 */
class Correction
{
    use UuidTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\DropZoneBundle\Entity\Drop",
     *     inversedBy="corrections"
     * )
     * @ORM\JoinColumn(name="drop_id", nullable=false, onDelete="CASCADE")
     *
     * @var Drop
     */
    protected $drop;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", nullable=true, onDelete="SET NULL")
     *
     * @var User
     */
    protected $user;

    /**
     * @ORM\Column(name="score", type="float", nullable=true)
     *
     * @var float
     */
    protected $score;

    /**
     * @ORM\Column(name="correction_comment", type="text", nullable=true)
     *
     * @var string
     */
    protected $comment;

    /**
     * @ORM\Column(name="is_valid", type="boolean", nullable=false)
     *
     * @var bool
     */
    protected $valid = true;

    /**
     * @ORM\Column(name="start_date", type="datetime", nullable=false)
     *
     * @var \DateTime
     */
    protected $startDate;

    /**
     * @ORM\Column(name="last_edition_date", type="datetime", nullable=false)
     *
     * @var \DateTime
     */
    protected $lastEditionDate;

    /**
     * @ORM\Column(name="end_date", type="datetime", nullable=true)
     *
     * @var \DateTime
     */
    protected $endDate;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     *
     * @var bool
     */
    protected $finished = false;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     *
     * @var bool
     */
    protected $editable = false;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     *
     * @var bool
     */
    protected $reported = false;

    /**
     * @ORM\Column(name="reported_comment", type="text", nullable=true)
     *
     * @var string
     */
    protected $reportedComment;

    /**
     * @ORM\Column(name="correction_denied", type="boolean", nullable=false)
     *
     * @var bool
     */
    protected $correctionDenied = false;

    /**
     * @ORM\Column(name="correction_denied_comment", type="text", nullable=true)
     *
     * @var string
     */
    protected $correctionDeniedComment;

    /**
     * @ORM\Column(name="team_id", type="integer", nullable=true)
     *
     * @var int
     */
    protected $teamId;

    /**
     * @ORM\Column(name="team_name", nullable=true)
     *
     * @var string
     */
    protected $teamName;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\DropZoneBundle\Entity\Grade",
     *     mappedBy="correction",
     *     cascade={"persist", "remove"}
     * )
     *
     * @var Grade
     */
    protected $grades;

    /**
     * Correction constructor.
     */
    public function __construct()
    {
        $this->refreshUuid();
        $this->grades = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return Drop
     */
    public function getDrop()
    {
        return $this->drop;
    }

    /**
     * @param Drop $drop
     */
    public function setDrop(Drop $drop)
    {
        $this->drop = $drop;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User|null $user
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;
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
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return $this->valid;
    }

    /**
     * @param bool $valid
     */
    public function setValid($valid)
    {
        $this->valid = $valid;
    }

    /**
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param \DateTime $startDate
     */
    public function setStartDate(\DateTime $startDate)
    {
        $this->startDate = $startDate;
    }

    /**
     * @return \DateTime
     */
    public function getLastEditionDate()
    {
        return $this->lastEditionDate;
    }

    /**
     * @param \DateTime $lastEditionDate
     */
    public function setLastEditionDate(\DateTime $lastEditionDate)
    {
        $this->lastEditionDate = $lastEditionDate;
    }

    /**
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param \DateTime|null $endDate
     */
    public function setEndDate(\DateTime $endDate = null)
    {
        $this->endDate = $endDate;
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
    public function isEditable()
    {
        return $this->editable;
    }

    /**
     * @param bool $editable
     */
    public function setEditable($editable)
    {
        $this->editable = $editable;
    }

    /**
     * @return bool
     */
    public function isReported()
    {
        return $this->reported;
    }

    /**
     * @param bool $reported
     */
    public function setReported($reported)
    {
        $this->reported = $reported;
    }

    /**
     * @return string
     */
    public function getReportedComment()
    {
        return $this->reportedComment;
    }

    /**
     * @param string $reportedComment
     */
    public function setReportedComment($reportedComment)
    {
        $this->reportedComment = $reportedComment;
    }

    /**
     * @return bool
     */
    public function isCorrectionDenied()
    {
        return $this->correctionDenied;
    }

    /**
     * @param bool $correctionDenied
     */
    public function setCorrectionDenied($correctionDenied)
    {
        $this->correctionDenied = $correctionDenied;
    }

    /**
     * @return string
     */
    public function getCorrectionDeniedComment()
    {
        return $this->correctionDeniedComment;
    }

    /**
     * @param string $correctionDeniedComment
     */
    public function setCorrectionDeniedComment($correctionDeniedComment)
    {
        $this->correctionDeniedComment = $correctionDeniedComment;
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
     * @return array
     */
    public function getGrades()
    {
        return $this->grades->toArray();
    }

    /**
     * @param Grade $grade
     */
    public function addGrade(Grade $grade)
    {
        if (!$this->grades->contains($grade)) {
            $this->grades->add($grade);
        }
    }

    /**
     * @param Grade $grade
     */
    public function removeGrade(Grade $grade)
    {
        if ($this->grades->contains($grade)) {
            $this->grades->removeElement($grade);
        }
    }

    public function emptyGrades()
    {
        $this->grades->clear();
    }

    /**
     * @param bool $hydrateUser
     *
     * @return array
     */
    public function toArray($hydrateUser)
    {
        $json = [
            'id' => $this->getId(),
            'editable' => $this->isEditable(),
        ];

        if (true === $this->isFinished()) {
            $json['valid'] = $this->isValid();
            $json['score'] = $this->getScore();
            $json['comment'] = $this->getComment();
            $json['reportedComment'] = $this->getReportedComment();
        }

        if (true === $hydrateUser) {
            $json['user'] = [
                'id' => $this->getUser()->getId(),
                'lastName' => $this->getUser()->getLastName(),
                'firstName' => $this->getUser()->getFirstName(),
                'username' => $this->getUser()->getUsername(),
            ];
        }

        return $json;
    }
}
