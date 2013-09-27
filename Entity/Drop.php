<?php
/**
 * Created by : Vincent SAISSET
 * Date: 21/08/13
 * Time: 15:39
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
class Drop {
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

    public function __construct()
    {
        $this->documents = new ArrayCollection();
    }

    /**
     * @return Document
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * @param Document $documents
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
     * @param mixed $id
     */
    public function getId()
    {
        return $this->id;
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

    public function getCalculatedGrade()
    {
        $grade = 0;
        $nbFinishedCorrections = 0;

        if (count($this->getCorrections()) > 0) {
            foreach ($this->getCorrections() as $correction) {
                if ($correction->getFinished() and $correction->getValid() and $correction->getTotalGrade() != null) {
                    $grade = $grade + $correction->getTotalGrade();
                    $nbFinishedCorrections = $nbFinishedCorrections + 1;
                }
            }
        }

        if ($nbFinishedCorrections > 0) {
            $grade = number_format(($grade / $nbFinishedCorrections));
        } else {
            $grade = -1;
        }

        return $grade;
    }

    public function countFinishedCorrections()
    {
        $nbFinishedCorrections = 0;
        foreach ($this->getCorrections() as $correction) {
            if ($correction->getFinished() and $correction->getValid() and $correction->getTotalGrade() != null) {
                $nbFinishedCorrections = $nbFinishedCorrections + 1;
            }
        }

        return $nbFinishedCorrections;
    }
}