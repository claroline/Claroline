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

use Doctrine\DBAL\Types\Types;
use Claroline\DropZoneBundle\Repository\CorrectionRepository;
use DateTime;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\CoreBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_dropzonebundle_correction')]
#[ORM\Entity(repositoryClass: CorrectionRepository::class)]
class Correction
{
    use Id;
    use Uuid;

    /**
     *
     * @var Drop
     */
    #[ORM\JoinColumn(name: 'drop_id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Drop::class, inversedBy: 'corrections')]
    protected $drop;

    /**
     *
     * @var User
     */
    #[ORM\JoinColumn(name: 'user_id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: User::class)]
    protected $user;

    /**
     * @var float
     */
    #[ORM\Column(name: 'score', type: Types::FLOAT, nullable: true)]
    protected $score;

    /**
     * @var string
     */
    #[ORM\Column(name: 'correction_comment', type: Types::TEXT, nullable: true)]
    protected $comment;

    /**
     * @var bool
     */
    #[ORM\Column(name: 'is_valid', type: Types::BOOLEAN, nullable: false)]
    protected $valid = true;

    /**
     * @var DateTime
     */
    #[ORM\Column(name: 'start_date', type: Types::DATETIME_MUTABLE, nullable: false)]
    protected $startDate;

    /**
     * @var DateTime
     */
    #[ORM\Column(name: 'last_edition_date', type: Types::DATETIME_MUTABLE, nullable: false)]
    protected $lastEditionDate;

    /**
     * @var DateTime
     */
    #[ORM\Column(name: 'end_date', type: Types::DATETIME_MUTABLE, nullable: true)]
    protected $endDate;

    /**
     * @var bool
     */
    #[ORM\Column(type: Types::BOOLEAN, nullable: false)]
    protected $finished = false;

    /**
     * @var bool
     */
    #[ORM\Column(type: Types::BOOLEAN, nullable: false)]
    protected $editable = false;

    /**
     * @var bool
     */
    #[ORM\Column(type: Types::BOOLEAN, nullable: false)]
    protected $reported = false;

    /**
     * @var string
     */
    #[ORM\Column(name: 'reported_comment', type: Types::TEXT, nullable: true)]
    protected $reportedComment;

    /**
     * @var bool
     */
    #[ORM\Column(name: 'correction_denied', type: Types::BOOLEAN, nullable: false)]
    protected $correctionDenied = false;

    /**
     * @var string
     */
    #[ORM\Column(name: 'correction_denied_comment', type: Types::TEXT, nullable: true)]
    protected $correctionDeniedComment;

    /**
     * @var int
     */
    #[ORM\Column(name: 'team_id', type: Types::INTEGER, nullable: true)]
    protected $teamId;

    /**
     * @var string
     */
    #[ORM\Column(name: 'team_uuid', nullable: true)]
    protected $teamUuid;

    /**
     * @var string
     */
    #[ORM\Column(name: 'team_name', nullable: true)]
    protected $teamName;

    /**
     * @var Grade
     */
    #[ORM\OneToMany(targetEntity: Grade::class, mappedBy: 'correction', cascade: ['persist', 'remove'])]
    protected $grades;

    /**
     * Correction constructor.
     */
    public function __construct()
    {
        $this->refreshUuid();
        $this->grades = new ArrayCollection();
        $currentDate = new DateTime();
        $this->setStartDate($currentDate);
        $this->setLastEditionDate($currentDate);
    }

    /**
     * @return Drop
     */
    public function getDrop()
    {
        return $this->drop;
    }

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
     * @return DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    public function setStartDate(DateTime $startDate)
    {
        $this->startDate = $startDate;
    }

    /**
     * @return DateTime
     */
    public function getLastEditionDate()
    {
        return $this->lastEditionDate;
    }

    public function setLastEditionDate(DateTime $lastEditionDate)
    {
        $this->lastEditionDate = $lastEditionDate;
    }

    /**
     * @return DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    public function setEndDate(DateTime $endDate = null)
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
     * @return array
     */
    public function getGrades()
    {
        return $this->grades->toArray();
    }

    public function addGrade(Grade $grade)
    {
        if (!$this->grades->contains($grade)) {
            $this->grades->add($grade);
        }
    }

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
