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
 * @ORM\Entity
 * @ORM\Table(name="claro_dropzonebundle_revision")
 */
class Revision
{
    use UuidTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\DropZoneBundle\Entity\Drop",
     *     inversedBy="revisions"
     * )
     * @ORM\JoinColumn(name="drop_id", nullable=false, onDelete="CASCADE")
     */
    protected $drop;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="creator_id", nullable=true, onDelete="SET NULL")
     */
    protected $creator;

    /**
     * @ORM\Column(name="creation_date", type="datetime", nullable=false)
     */
    protected $creationDate;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\DropZoneBundle\Entity\Document",
     *     mappedBy="revision"
     * )
     */
    protected $documents;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\DropZoneBundle\Entity\RevisionComment",
     *     mappedBy="revision"
     * )
     */
    protected $comments;

    /**
     * Revision constructor.
     */
    public function __construct()
    {
        $this->refreshUuid();
        $this->setCreationDate(new \DateTime());
        $this->documents = new ArrayCollection();
        $this->comments = new ArrayCollection();
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
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * @param User|null $creator
     */
    public function setCreator(User $creator = null)
    {
        $this->creator = $creator;
    }

    /**
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @param \DateTime $creationDate
     */
    public function setCreationDate(\DateTime $creationDate)
    {
        $this->creationDate = $creationDate;
    }

    /**
     * @return ArrayCollection
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * @return ArrayCollection
     */
    public function getComments()
    {
        return $this->comments;
    }
}
