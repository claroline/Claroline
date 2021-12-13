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

/**
 * @ORM\MappedSuperclass
 */
abstract class AbstractComment
{
    use Id;
    use Uuid;

    /**
     * @ORM\Column(type="text")
     */
    protected $content;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", nullable=true, onDelete="SET NULL")
     */
    protected $user;

    /**
     * @ORM\Column(name="creation_date", type="datetime")
     */
    protected $creationDate;

    /**
     * @ORM\Column(name="edition_date", type="datetime", nullable=true)
     */
    protected $editionDate;

    public function __construct()
    {
        $this->refreshUuid();
        $this->setCreationDate(new \DateTime());
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content)
    {
        $this->content = $content;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user = null)
    {
        $this->user = $user;
    }

    public function getCreationDate(): ?\DateTimeInterface
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTimeInterface $creationDate)
    {
        $this->creationDate = $creationDate;
    }

    public function getEditionDate(): ?\DateTimeInterface
    {
        return $this->editionDate;
    }

    public function setEditionDate(?\DateTimeInterface $editionDate = null)
    {
        $this->editionDate = $editionDate;
    }
}
