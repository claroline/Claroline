<?php

namespace Claroline\ClacoFormBundle\Entity;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Claroline\ClacoFormBundle\Repository\CommentRepository")
 * @ORM\Table(name="claro_clacoformbundle_comment")
 */
class Comment
{
    const PENDING = 0;
    const VALIDATED = 1;
    const BLOCKED = 2;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"api_claco_form", "api_user_min"})
     * @SerializedName("id")
     */
    protected $id;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     * @Groups({"api_claco_form", "api_user_min"})
     * @SerializedName("content")
     */
    protected $content;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", onDelete="SET NULL", nullable=true)
     * @Groups({"api_user_min"})
     * @SerializedName("user")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\ClacoFormBundle\Entity\Entry",
     *     inversedBy="comments"
     * )
     * @ORM\JoinColumn(name="entry_id", onDelete="CASCADE")
     */
    protected $entry;

    /**
     * @ORM\Column(name="creation_date", type="datetime")
     * @Groups({"api_claco_form", "api_user_min"})
     * @SerializedName("creationDate")
     */
    protected $creationDate;

    /**
     * @ORM\Column(name="edition_date", type="datetime", nullable=true)
     * @Groups({"api_claco_form", "api_user_min"})
     * @SerializedName("editionDate")
     */
    protected $editionDate;

    /**
     * @ORM\Column(name="comment_status", type="integer")
     * @Groups({"api_claco_form", "api_user_min"})
     * @SerializedName("status")
     */
    protected $status;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user = null)
    {
        $this->user = $user;
    }

    public function getEntry()
    {
        return $this->entry;
    }

    public function setEntry(Entry $entry)
    {
        $this->entry = $entry;
    }

    public function getCreationDate()
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTime $creationDate)
    {
        $this->creationDate = $creationDate;
    }

    public function getEditionDate()
    {
        return $this->editionDate;
    }

    public function setEditionDate(\DateTime $editionDate = null)
    {
        $this->editionDate = $editionDate;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }
}
