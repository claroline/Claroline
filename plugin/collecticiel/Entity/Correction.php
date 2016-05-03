<?php
/**
 * Created by : Vincent SAISSET
 * Date: 21/08/13
 * Time: 16:40.
 */

namespace Innova\CollecticielBundle\Entity;

use Claroline\CoreBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="Innova\CollecticielBundle\Repository\CorrectionRepository")
 * @ORM\Table(name="innova_collecticielbundle_correction")
 */
class Correction
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="total_grade", type="decimal", scale=2, nullable=true)
     * @Assert\Range(
     *      min = 0,
     *      max = 20
     * )
     */
    protected $totalGrade = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $comment = null;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    protected $valid = true;

    /**
     * @ORM\Column(name="start_date", type="datetime", nullable=false)
     * @Gedmo\Timestampable(on="create")
     */
    protected $startDate;

    /**
     * @ORM\Column(name="last_open_date", type="datetime", nullable=false)
     * @Gedmo\Timestampable(on="create")
     */
    protected $lastOpenDate;

    /**
     * @ORM\Column(name="end_date", type="datetime", nullable=true)
     */
    protected $endDate = null;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    protected $finished = false;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    protected $editable = false;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    protected $reporter = false;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $reportComment = null;

    /**
     * @ORM\Column(type="boolean",nullable=false)
     * In the case where the student don't agree the correction, he can flag it ( as the corrector can report the copy)
     */
    protected $correctionDenied = false;

    /**
     * @ORM\Column(type="text",nullable=true)
     */
    protected $correctionDeniedComment = null;

    /**
     * @ORM\OneToMany(
     *      targetEntity="Innova\CollecticielBundle\Entity\Grade",
     *      mappedBy="correction",
     *      cascade={"all"},
     *      orphanRemoval=true
     * )
     */
    protected $grades;

    /**
     * @ORM\ManyToOne(
     *      targetEntity="Claroline\CoreBundle\Entity\User"
     * )
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $user;

    /**
     * @ORM\ManyToOne(
     *      targetEntity="Innova\CollecticielBundle\Entity\Drop",
     *      inversedBy="corrections"
     * )
     * @ORM\JoinColumn(name="drop_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    protected $drop;

    /**
     * @ORM\ManyToOne(
     *      targetEntity="Innova\CollecticielBundle\Entity\Dropzone"
     * )
     * @ORM\JoinColumn(name="drop_zone_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $dropzone;

    public function __construct()
    {
        $this->grades = new ArrayCollection();
    }

    /**
     * @param mixed $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * @return mixed
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param mixed $endDate
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    }

    /**
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param mixed $finished
     */
    public function setFinished($finished)
    {
        $this->finished = $finished;
    }

    /**
     * @return mixed
     */
    public function getFinished()
    {
        return $this->finished;
    }

    /**
     * @param mixed $grades
     */
    public function setGrades($grades)
    {
        $this->grades = $grades;
    }

    /**
     * @return mixed
     */
    public function getGrades()
    {
        return $this->grades;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $startDate
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    }

    /**
     * @return mixed
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param mixed $totalGrade
     */
    public function setTotalGrade($totalGrade)
    {
        $this->totalGrade = $totalGrade;
    }

    /**
     * @return mixed
     */
    public function getTotalGrade()
    {
        return $this->totalGrade;
    }

    /**
     * @param User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $valid
     */
    public function setValid($valid)
    {
        $this->valid = $valid;
    }

    /**
     * @return mixed
     */
    public function getValid()
    {
        return $this->valid;
    }

    /**
     * @param Drop $drop
     */
    public function setDrop($drop)
    {
        $this->drop = $drop;
    }

    /**
     * @return Drop
     */
    public function getDrop()
    {
        return $this->drop;
    }

    /**
     * @param mixed $lastOpenDate
     */
    public function setLastOpenDate($lastOpenDate)
    {
        $this->lastOpenDate = $lastOpenDate;
    }

    /**
     * @return mixed
     */
    public function getLastOpenDate()
    {
        return $this->lastOpenDate;
    }

    /**
     * @param Dropzone $dropzone
     */
    public function setDropzone($dropzone)
    {
        $this->dropzone = $dropzone;
    }

    /**
     * @return Dropzone
     */
    public function getDropzone()
    {
        return $this->dropzone;
    }

    /**
     * @param mixed $editable
     */
    public function setEditable($editable)
    {
        $this->editable = $editable;
    }

    /**
     * @return mixed
     */
    public function getEditable()
    {
        return $this->editable;
    }

    /**
     * @param mixed $reporter
     */
    public function setReporter($reporter)
    {
        $this->reporter = $reporter;
    }

    /**
     * @return mixed
     */
    public function getReporter()
    {
        return $this->reporter;
    }

    /**
     * @param mixed $reportComment
     */
    public function setReportComment($reportComment)
    {
        $this->reportComment = $reportComment;
    }

    /**
     * @return mixed
     */
    public function getReportComment()
    {
        return $this->reportComment;
    }

    /**
     * Set correctionDenied.
     *
     * @param bool $correctionDenied
     *
     * @return Correction
     */
    public function setCorrectionDenied($correctionDenied)
    {
        $this->correctionDenied = $correctionDenied;

        return $this;
    }

    /**
     * Get correctionDenied.
     *
     * @return bool
     */
    public function getCorrectionDenied()
    {
        return $this->correctionDenied;
    }

    /**
     * Set correctionDeniedComment.
     *
     * @param string $correctionDeniedComment
     *
     * @return Correction
     */
    public function setCorrectionDeniedComment($correctionDeniedComment)
    {
        $this->correctionDeniedComment = $correctionDeniedComment;

        return $this;
    }

    /**
     * Get correctionDeniedComment.
     *
     * @return string
     */
    public function getCorrectionDeniedComment()
    {
        return $this->correctionDeniedComment;
    }

    /**
     * @param bool $hydrateUser
     *
     * @return array
     */
    public function toArray($hydrateUser)
    {
        $json = array(
            'id' => $this->getId(),
            'editable' => $this->getEditable(),
        );

        if ($this->getFinished() === true) {
            $json['valid'] = $this->getValid();
            $json['totalGrade'] = $this->getTotalGrade();
            $json['comment'] = $this->getComment();
            $json['reporter'] = $this->getReporter();
            $json['reportComment'] = $this->getReportComment();
        }

        if ($hydrateUser === true) {
            $json['user'] = array(
                'id' => $this->getUser()->getId(),
                'lastName' => $this->getUser()->getLastName(),
                'firstName' => $this->getUser()->getFirstName(),
                'username' => $this->getUser()->getUsername(),
            );
        }

        return $json;
    }

    /**
     * Add grades.
     *
     * @param \Innova\CollecticielBundle\Entity\Grade $grades
     *
     * @return Correction
     */
    public function addGrade(\Innova\CollecticielBundle\Entity\Grade $grades)
    {
        $this->grades[] = $grades;

        return $this;
    }

    /**
     * Remove grades.
     *
     * @param \Innova\CollecticielBundle\Entity\Grade $grades
     */
    public function removeGrade(\Innova\CollecticielBundle\Entity\Grade $grades)
    {
        $this->grades->removeElement($grades);
    }
}
