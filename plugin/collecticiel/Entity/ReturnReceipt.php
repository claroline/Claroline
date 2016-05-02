<?php
/**
 * Created by : VINCENT Eric
 * Date: 10/05/2015.
*/

namespace Innova\CollecticielBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="Innova\CollecticielBundle\Repository\ReturnReceiptRepository")
 * @ORM\Table(name="innova_collecticielbundle_return_receipt")
 */
class ReturnReceipt
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Lien avec la table Document.
     */
    /**
     * @ORM\ManyToOne(
     *      targetEntity="Innova\CollecticielBundle\Entity\Document"
     * )
     * @ORM\JoinColumn(name="document_id", referencedColumnName="id", nullable=false)
     */
    protected $document;

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
     * Lien avec la table Dropzone.
     */
    /**
     * @ORM\ManyToOne(
     *      targetEntity="Innova\CollecticielBundle\Entity\Dropzone"
     * )
     * @ORM\JoinColumn(name="dropzone_id", referencedColumnName="id", nullable=false)
     */
    protected $dropzone;

    /**
     * @ORM\Column(name="return_receipt_date", type="datetime", nullable=false)
     * @Gedmo\Timestampable(on="create")
     */
    protected $returnReceiptDate;

    /**
     * Lien avec la table ReturnReceiptType.
     */
    /**
     * @ORM\ManyToOne(
     *      targetEntity="Innova\CollecticielBundle\Entity\ReturnReceiptType",
     *      inversedBy="returnreceipts"
     * )
     * @ORM\JoinColumn(name="return_receipt_type_id", onDelete="CASCADE")
     */
    protected $returnReceiptType;

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
     * Set returnReceiptDate.
     *
     * @param \DateTime $returnReceiptDate
     *
     * @return ReturnReceipt
     */
    public function setReturnReceiptDate($returnReceiptDate)
    {
        $this->returnReceiptDate = $returnReceiptDate;

        return $this;
    }

    /**
     * Get returnReceiptDate.
     *
     * @return \DateTime
     */
    public function getReturnReceiptDate()
    {
        return $this->returnReceiptDate;
    }

    /**
     * Set document.
     *
     * @param \Innova\CollecticielBundle\Entity\Document $document
     *
     * @return ReturnReceipt
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
     * Set user.
     *
     * @param \Claroline\CoreBundle\Entity\User $user
     *
     * @return ReturnReceipt
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
     * Set dropzone.
     *
     * @param \Innova\CollecticielBundle\Entity\Dropzone $dropzone
     *
     * @return ReturnReceipt
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
     * Set returnReceiptType.
     *
     * @param \Innova\CollecticielBundle\Entity\ReturnReceiptType $returnReceiptType
     *
     * @return ReturnReceipt
     */
    public function setReturnReceiptType(\Innova\CollecticielBundle\Entity\ReturnReceiptType $returnReceiptType = null)
    {
        $this->returnReceiptType = $returnReceiptType;

        return $this;
    }

    /**
     * Get returnReceiptType.
     *
     * @return \Innova\CollecticielBundle\Entity\ReturnReceiptType
     */
    public function getReturnReceiptType()
    {
        return $this->returnReceiptType;
    }
}
