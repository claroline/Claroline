<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ForumBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Claroline\CoreBundle\Entity\User;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_forum_message")
 * @ORM\Entity(repositoryClass="Claroline\ForumBundle\Repository\MessageRepository")
 */
class Message
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="content", type="text")
     * @Assert\NotBlank()
     */
    protected $content;

    /**
     * @ORM\Column(name="created", type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    protected $creationDate;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    protected $updated;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\ForumBundle\Entity\Subject",
     *     inversedBy="messages"
     * )
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $subject;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="user_id")
     */
    protected $creator;

    /**
     * @ORM\Column(nullable=true)
     */
    protected $author;

    /**
     * Returns the resource id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setSubject(Subject $subject)
    {
        $this->subject = $subject;
    }

    public function getSubject()
    {
        return $this->subject;
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
