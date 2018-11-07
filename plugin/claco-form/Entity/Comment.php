<?php

namespace Claroline\ClacoFormBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Claroline\ClacoFormBundle\Repository\CommentRepository")
 * @ORM\Table(name="claro_clacoformbundle_comment")
 */
class Comment
{
    use Id;
    use Uuid;

    const PENDING = 0;
    const VALIDATED = 1;
    const BLOCKED = 2;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     *
     * @var string
     */
    protected $content;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", onDelete="SET NULL", nullable=true)
     *
     * @var User
     */
    protected $user;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\ClacoFormBundle\Entity\Entry",
     *     inversedBy="comments"
     * )
     * @ORM\JoinColumn(name="entry_id", onDelete="CASCADE")
     *
     * @var Entry
     */
    protected $entry;

    /**
     * @ORM\Column(name="creation_date", type="datetime")
     *
     * @var \DateTime
     */
    protected $creationDate;

    /**
     * @ORM\Column(name="edition_date", type="datetime", nullable=true)
     *
     * @var \DateTime
     */
    protected $editionDate;

    /**
     * @ORM\Column(name="comment_status", type="integer")
     *
     * @var int
     */
    protected $status;

    /**
     * Comment constructor.
     */
    public function __construct()
    {
        $this->refreshUuid();
    }

    /**
     * Get content.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set content.
     *
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Get user.
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set user.
     *
     * @param User|null $user
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;
    }

    /**
     * Get entry.
     *
     * @return Entry
     */
    public function getEntry()
    {
        return $this->entry;
    }

    /**
     * Set entry.
     *
     * @param Entry $entry
     */
    public function setEntry(Entry $entry)
    {
        $this->entry = $entry;
    }

    /**
     * Get creation date.
     *
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Set creation date.
     *
     * @param \DateTime $creationDate
     */
    public function setCreationDate(\DateTime $creationDate)
    {
        $this->creationDate = $creationDate;
    }

    /**
     * Get edition date.
     *
     * @return \DateTime
     */
    public function getEditionDate()
    {
        return $this->editionDate;
    }

    /**
     * Set edition date.
     *
     * @param \DateTime|null $editionDate
     */
    public function setEditionDate(\DateTime $editionDate = null)
    {
        $this->editionDate = $editionDate;
    }

    /**
     * Get status.
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set status.
     *
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }
}
