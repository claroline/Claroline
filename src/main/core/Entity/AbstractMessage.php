<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\MappedSuperclass]
abstract class AbstractMessage
{
    use Id;
    use Uuid;

    #[ORM\Column(name: 'content', type: 'text')]
    protected $content;

    #[ORM\Column(name: 'created', type: 'datetime')]
    #[Gedmo\Timestampable(on: 'create')]
    protected $creationDate;

    #[ORM\Column(type: 'datetime')]
    #[Gedmo\Timestampable(on: 'update')]
    protected $updated;

    /**
     *
     * @var User
     */
    #[ORM\JoinColumn(name: 'user_id', onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: \Claroline\CoreBundle\Entity\User::class, cascade: ['persist'])]
    protected $creator;

    #[ORM\Column(nullable: true)]
    protected $author;

    public function __construct()
    {
        $this->creationDate = new \DateTime();
        $this->updated = new \DateTime();
        $this->refreshUuid();
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getContent()
    {
        return $this->content;
    }

    /**
     * Sets the message creator.
     *
     * @param \Claroline\CoreBundle\Entity\User
     */
    public function setCreator(User $creator)
    {
        $this->creator = $creator;
    }

    /**
     * @return User
     */
    public function getCreator()
    {
        return $this->creator;
    }

    public function getCreationDate()
    {
        return $this->creationDate;
    }

    public function setCreationDate($date)
    {
        $this->creationDate = $date;
    }

    public function setModificationDate($date)
    {
        $this->updated = $date;
    }

    public function getModificationDate()
    {
        return $this->updated;
    }

    public function getAuthor()
    {
        return $this->author;
    }

    public function setAuthor($author)
    {
        $this->author = $author;
    }
}
