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

#[ORM\Table(name: 'claro_dropzonebundle_revision')]
#[ORM\Entity]
class Revision
{
    use Id;
    use Uuid;

    #[ORM\JoinColumn(name: 'drop_id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: \Claroline\DropZoneBundle\Entity\Drop::class, inversedBy: 'revisions')]
    protected $drop;

    #[ORM\JoinColumn(name: 'creator_id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: \Claroline\CoreBundle\Entity\User::class)]
    protected $creator;

    #[ORM\Column(name: 'creation_date', type: 'datetime', nullable: false)]
    protected $creationDate;

    #[ORM\OneToMany(targetEntity: \Claroline\DropZoneBundle\Entity\Document::class, mappedBy: 'revision')]
    protected $documents;

    #[ORM\OneToMany(targetEntity: \Claroline\DropZoneBundle\Entity\RevisionComment::class, mappedBy: 'revision')]
    protected $comments;

    public function __construct()
    {
        $this->refreshUuid();
        $this->setCreationDate(new \DateTime());
        $this->documents = new ArrayCollection();
        $this->comments = new ArrayCollection();
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
    public function getCreator()
    {
        return $this->creator;
    }

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
