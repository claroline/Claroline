<?php
/**
 * Created by : Eric VINCENT
 * Date: 05/2016.
 * Modify by : Add recordOrTransmit (05/2016).
 * Modify by : Add appreciation (06/2016).
 */

namespace Innova\CollecticielBundle\Entity;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="innova_collecticielbundle_notation")
 */
class Notation
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Lien avec la table User.
     */
    /**
     * @ORM\ManyToOne(
     *      targetEntity="Claroline\CoreBundle\Entity\User"
     * )
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $user;

    /**
     * Lien avec la table Document.
     */
    /**
     * @ORM\ManyToOne(
     *      targetEntity="Innova\CollecticielBundle\Entity\Document",
     *      inversedBy="notations"
     * )
     * @ORM\JoinColumn(name="document_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $document;

    /**
     * Lien avec la table Dropzone.
     */
    /**
     * @ORM\ManyToOne(
     *      targetEntity="Innova\CollecticielBundle\Entity\Dropzone",
     *      inversedBy="notations"
     * )
     * @ORM\JoinColumn(name="dropzone_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $dropzone;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Innova\CollecticielBundle\Entity\ChoiceCriteria",
     *     mappedBy="notation",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
     */
    protected $choiceCriterias;

    /**
     * @ORM\Column(name="note", type="integer", nullable=false)
     */
    protected $note = 0;

    /**
     * @ORM\Column(name="appreciation", type="integer", nullable=false)
     */
    protected $appreciation = 0;

    /**
     * @ORM\Column(name="comment_text",type="text", nullable=true)
     */
    protected $commentText = null;

    /**
     * @ORM\Column(name="quality_text",type="text", nullable=true)
     */
    protected $qualityText = null;

    /**
     * @ORM\Column(name="note_date", type="datetime", nullable=false)
     * @Gedmo\Timestampable(on="create")
     */
    protected $noteDate;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    protected $recordOrTransmit = false;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set note.
     *
     * @param int $note
     *
     * @return Notation
     */
    public function setNote($note)
    {
        $this->note = $note;

        return $this;
    }

    /**
     * Get note.
     *
     * @return int
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * Set commentText.
     *
     * @param string $commentText
     *
     * @return Notation
     */
    public function setCommentText($commentText)
    {
        $this->commentText = $commentText;

        return $this;
    }

    /**
     * Get commentText.
     *
     * @return string
     */
    public function getCommentText()
    {
        return $this->commentText;
    }

    /**
     * Set qualityText.
     *
     * @param string $qualityText
     *
     * @return Notation
     */
    public function setQualityText($qualityText)
    {
        $this->qualityText = $qualityText;

        return $this;
    }

    /**
     * Get qualityText.
     *
     * @return string
     */
    public function getQualityText()
    {
        return $this->qualityText;
    }

    /**
     * Set noteDate.
     *
     * @param \DateTime $noteDate
     *
     * @return Notation
     */
    public function setNoteDate($noteDate)
    {
        $this->noteDate = $noteDate;

        return $this;
    }

    /**
     * Get noteDate.
     *
     * @return \DateTime
     */
    public function getNoteDate()
    {
        return $this->noteDate;
    }

    /**
     * Set user.
     *
     * @param \Claroline\CoreBundle\Entity\User $user
     *
     * @return Notation
     */
    public function setUser(\Claroline\CoreBundle\Entity\User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     *
     * @return \Claroline\CoreBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set document.
     *
     * @param \Innova\CollecticielBundle\Entity\Document $document
     *
     * @return Notation
     */
    public function setDocument(\Innova\CollecticielBundle\Entity\Document $document)
    {
        $this->document = $document;

        return $this;
    }

    /**
     * Get document.
     *
     * @return \Innova\CollecticielBundle\Entity\Document
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Set dropzone.
     *
     * @param \Innova\CollecticielBundle\Entity\Dropzone $dropzone
     *
     * @return Notation
     */
    public function setDropzone(\Innova\CollecticielBundle\Entity\Dropzone $dropzone)
    {
        $this->dropzone = $dropzone;

        return $this;
    }

    /**
     * Get dropzone.
     *
     * @return \Innova\CollecticielBundle\Entity\Dropzone
     */
    public function getDropzone()
    {
        return $this->dropzone;
    }

    /**
     * Set recordOrTransmit.
     *
     * @param bool $recordOrTransmit
     *
     * @return Notation
     */
    public function setRecordOrTransmit($recordOrTransmit)
    {
        $this->recordOrTransmit = $recordOrTransmit;

        return $this;
    }

    /**
     * Get recordOrTransmit.
     *
     * @return bool
     */
    public function getRecordOrTransmit()
    {
        return $this->recordOrTransmit;
    }

    /**
     * Set appreciation.
     *
     * @param int $appreciation
     *
     * @return Notation
     */
    public function setAppreciation($appreciation)
    {
        $this->appreciation = $appreciation;

        return $this;
    }

    /**
     * Get appreciation.
     *
     * @return int
     */
    public function getAppreciation()
    {
        return $this->appreciation;
    }
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->choiceCriterias = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add choiceCriteria.
     *
     * @param \Innova\CollecticielBundle\Entity\ChoiceCriteria $choiceCriteria
     *
     * @return Notation
     */
    public function addChoiceCriteria(\Innova\CollecticielBundle\Entity\ChoiceCriteria $choiceCriteria)
    {
        $this->choiceCriterias[] = $choiceCriteria;

        return $this;
    }

    /**
     * Remove choiceCriteria.
     *
     * @param \Innova\CollecticielBundle\Entity\ChoiceCriteria $choiceCriteria
     */
    public function removeChoiceCriteria(\Innova\CollecticielBundle\Entity\ChoiceCriteria $choiceCriteria)
    {
        $this->choiceCriterias->removeElement($choiceCriteria);
    }

    /**
     * Get choiceCriterias.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChoiceCriterias()
    {
        return $this->choiceCriterias;
    }
}
