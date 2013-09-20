<?php
/**
 * Created by : Vincent SAISSET
 * Date: 21/08/13
 * Time: 15:39
 */

namespace Icap\DropZoneBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="Icap\DropZoneBundle\Repository\DropRepository")
 * @ORM\Table(name="icap__dropzonebundle_drop", uniqueConstraints={@ORM\UniqueConstraint(name="unique_drop_for_user_in_drop_zone", columns={"drop_zone_id", "user_id"})})
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
     * @ORM\ManyToOne(
     *      targetEntity="Icap\DropZoneBundle\Entity\DropZone",
     *      inversedBy="drops"
     * )
     * @ORM\JoinColumn(name="drop_zone_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $dropZone;
    /**
     * @ORM\OneToMany(
     *     targetEntity="Icap\DropZoneBundle\Entity\Document",
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
     *     targetEntity="Icap\DropZoneBundle\Entity\Correction",
     *     mappedBy="drop"
     * )
     */
    protected $corrections;

    public function __construct()
    {
        $this->documents = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * @param mixed $documents
     */
    public function setDocuments($documents)
    {
        $this->documents = $documents;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
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
     * @return mixed
     */
    public function getDropZone()
    {
        return $this->dropZone;
    }

    /**
     * @param mixed $dropZone
     */
    public function setDropZone($dropZone)
    {
        $this->dropZone = $dropZone;
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
}