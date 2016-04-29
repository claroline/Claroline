<?php
/**
 * Created by : Vincent SAISSET
 * Date: 21/08/13
 * Time: 15:39.
 */

namespace Icap\DropzoneBundle\Entity;

use Claroline\CoreBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="Icap\DropzoneBundle\Repository\DropRepository")
 * @ORM\Table(name="icap__dropzonebundle_drop", uniqueConstraints={
 *      @ORM\UniqueConstraint(name="unique_drop_for_user_in_drop_zone", columns={"drop_zone_id", "user_id"}),
 *      @ORM\UniqueConstraint(name="unique_drop_number_in_drop_zone", columns={"drop_zone_id", "number"})
 * })
 */
class Drop
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @ORM\Column(name="drop_date", type="datetime", nullable=false)
     * @Gedmo\Timestampable(on="create")
     */
    protected $dropDate;
    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    protected $reported = false;
    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    protected $finished = false;
    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $number = null;
    /**
     * @ORM\ManyToOne(
     *      targetEntity="Icap\DropzoneBundle\Entity\Dropzone",
     *      inversedBy="drops"
     * )
     * @ORM\JoinColumn(name="drop_zone_id", referencedColumnName="id", nullable=false)
     */
    protected $dropzone;
    /**
     * @ORM\OneToMany(
     *     targetEntity="Icap\DropzoneBundle\Entity\Document",
     *     mappedBy="drop",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
     */
    protected $documents;
    /**
     * @ORM\ManyToOne(
     *      targetEntity="Claroline\CoreBundle\Entity\User"
     * )
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $user;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Icap\DropzoneBundle\Entity\Correction",
     *     mappedBy="drop"
     * )
     */
    protected $corrections;

    /**
     * @ORM\OneToOne(
     *      targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode",
     *      cascade={"all"}
     * )
     * @ORM\JoinColumn(name="hidden_directory_id", referencedColumnName="id", nullable=true)
     */
    protected $hiddenDirectory;

    /**
     * Indicate if the drop was close automaticaly ( when time is up by the dropzone option
     * autoCloseOpenedDropsWhenTimeIsUp ).
     *
     * @ORM\Column(name="auto_closed_drop",type="boolean", nullable=false,options={"default" = 0})
     */
    protected $autoClosedDrop = 0;

    /**
     * @var bool
     *           Used to flag that a copy have been unlocked ( admin made a correction that unlocked the copy:
     *           the copy doesn't wait anymore the expected number of correction
     *
     * @ORM\Column(name="unlocked_drop",type="boolean",nullable=false,options={"default" = false})
     */
    protected $unlockedDrop = false;

    /**
     * @var bool
     *           Used to flag that a user have been unlocked ( admin made a correction that unlocked the copy:
     *           the drop author will not need anymore to do the expected number of correction to see his copy.)
     *
     * @ORM\Column(name="unlocked_user",type="boolean",nullable=false,options={"default" = false})
     */
    protected $unlockedUser = false;

    public function __construct()
    {
        $this->documents = new ArrayCollection();
    }

    /**
     * @return Document[]
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * @param Document[] $documents
     */
    public function setDocuments($documents)
    {
        $this->documents = $documents;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
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
     * @return mixed
     */
    public function getDropDate()
    {
        return $this->dropDate;
    }

    /**
     * @param mixed $dropDate
     */
    public function setDropDate($dropDate)
    {
        $this->dropDate = $dropDate;
    }

    /**
     * @return Dropzone
     */
    public function getDropzone()
    {
        return $this->dropzone;
    }

    /**
     * @param Dropzone $dropzone
     */
    public function setDropzone($dropzone)
    {
        $this->dropzone = $dropzone;
    }

    /**
     * @return mixed
     */
    public function getReported()
    {
        return $this->reported;
    }

    /**
     * @param mixed $reported
     */
    public function setReported($reported)
    {
        $this->reported = $reported;
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
     * @param mixed $corrections
     */
    public function setCorrections($corrections)
    {
        $this->corrections = $corrections;
    }

    /**
     * @return mixed
     */
    public function getCorrections()
    {
        return $this->corrections;
    }

    /**
     * @param mixed $hiddenDirectory
     */
    public function setHiddenDirectory($hiddenDirectory)
    {
        $this->hiddenDirectory = $hiddenDirectory;
    }

    /**
     * @return mixed
     */
    public function getHiddenDirectory()
    {
        return $this->hiddenDirectory;
    }

    /**
     * @param mixed $number
     */
    public function setNumber($number)
    {
        $this->number = $number;
    }

    /**
     * @return mixed
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @return int|string
     */
    public function getCalculatedGrade()
    {
        $grade = 0;
        $nbFinishedCorrections = 0;
        if (count($this->getCorrections()) > 0) {
            foreach ($this->getCorrections() as $correction) {
                if ($correction->getFinished() && $correction->getValid() && $correction->getTotalGrade() != null) {
                    $grade = $grade + $correction->getTotalGrade();
                    $nbFinishedCorrections = $nbFinishedCorrections + 1;
                }
            }
        }

        if ($nbFinishedCorrections > 0) {
            $grade = number_format(($grade / $nbFinishedCorrections), 2);
        } else {
            $grade = -1;
        }

        return $grade;
    }

    /**
     * /!\ Care it check if correction is valid Too.
     *
     * @return int
     */
    public function countFinishedCorrections()
    {
        $nbFinishedCorrections = 0;
        foreach ($this->getCorrections() as $correction) {
            if ($correction->getFinished() && $correction->getValid() && $correction->getTotalGrade() != null) {
                $nbFinishedCorrections = $nbFinishedCorrections + 1;
            }
        }

        return $nbFinishedCorrections;
    }

    public function getHasDeniedCorrection()
    {
        $hasDeniedCorrection = false;
        $corrections = $this->getCorrections();
        foreach ($corrections as $correction) {
            if ($correction->getCorrectionDenied()) {
                $hasDeniedCorrection = true;
                break;
            }
        }

        return $hasDeniedCorrection;
    }

    /**
     * @return \DateTime|false
     */
    public function getLastCorrectionDate()
    {
        /** @var Correction[] $corrections */
        $corrections = $this->getCorrections();

        $date = false;
        $validCorrectionFound = false;
        foreach ($corrections as $correction) {
            // if an ended  correction (with a endDate value) has not been found
            if ($validCorrectionFound == false) {
                // if its a valid correction.
                if ($correction->getEndDate() !== null) {
                    // valid correction found, we change the step and keep the date.
                    $date = $correction->getEndDate();
                    $validCorrectionFound = true;
                }
            } else {
                // at least a valid ended  correction has been found ( with an endDate)
                // date comparaison if $correction endDate is not NULL;
                if ($correction->getEndDate() !== null) {
                    if ($date->getTimestamp() < $correction->getEndDate()->getTimestamp()) {
                        $date = $correction->getEndDate();
                    }
                }
            }
        }

        return $date;
    }

    /**
     * @param mixed $autoClosedDrop
     */
    public function setAutoClosedDrop($autoClosedDrop)
    {
        $this->autoClosedDrop = $autoClosedDrop;
    }

    /**
     * @return mixed
     */
    public function getAutoClosedDrop()
    {
        return $this->autoClosedDrop;
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
    public function isUnlockedDrop()
    {
        return $this->unlockedDrop;
    }

    /**
     * @param bool $unlockedUser
     */
    public function setUnlockedUser($unlockedUser)
    {
        $this->unlockedUser = $unlockedUser;
    }

    /**
     * @return bool
     */
    public function getUnlockedUser()
    {
        return $this->unlockedUser;
    }
}
