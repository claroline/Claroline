<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ClacoFormBundle\Entity;

use Claroline\CoreBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Claroline\ClacoFormBundle\Repository\EntryRepository")
 * @ORM\Table(name="claro_clacoformbundle_entry")
 */
class Entry
{
    const PENDING = 0;
    const PUBLISHED = 1;
    const UNPUBLISHED = 2;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"api_claco_form", "api_user_min"})
     * @SerializedName("id")
     */
    protected $id;

    /**
     * @ORM\Column
     * @Assert\NotBlank()
     * @Groups({"api_claco_form", "api_user_min"})
     * @SerializedName("title")
     */
    protected $title;

    /**
     * @ORM\Column(name="entry_status", type="integer")
     * @Groups({"api_claco_form", "api_user_min"})
     * @SerializedName("status")
     */
    protected $status;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\ClacoFormBundle\Entity\ClacoForm",
     *     inversedBy="categories"
     * )
     * @ORM\JoinColumn(name="claco_form_id", nullable=false, onDelete="CASCADE")
     * @Groups({"api_claco_form"})
     * @SerializedName("clacoForm")
     */
    protected $clacoForm;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", nullable=true, onDelete="CASCADE")
     * @Groups({"api_user_min"})
     * @SerializedName("user")
     */
    protected $user;

    /**
     * @ORM\Column(name="creation_date", type="datetime", nullable=false)
     * @Groups({"api_claco_form", "api_user_min"})
     * @SerializedName("creationDate")
     */
    protected $creationDate;

    /**
     * @ORM\Column(name="edition_date", type="datetime", nullable=true)
     * @Groups({"api_claco_form", "api_user_min"})
     * @SerializedName("editionDate")
     */
    protected $editionDate = null;

    /**
     * @ORM\Column(name="publication_date", type="datetime", nullable=true)
     * @Groups({"api_claco_form", "api_user_min"})
     * @SerializedName("publicationDate")
     */
    protected $publicationDate = null;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\ClacoFormBundle\Entity\FieldValue",
     *     mappedBy="entry"
     * )
     * @ORM\JoinTable(name="claro_clacoformbundle_entry_value")
     * @Groups({"api_user_min"})
     * @SerializedName("fieldValues")
     */
    protected $fieldValues;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\ClacoFormBundle\Entity\Comment",
     *     mappedBy="entry"
     * )
     * @Groups({"api_claco_form", "api_user_min"})
     * @SerializedName("comments")
     */
    protected $comments;

    /**
     * @ORM\ManyToMany(targetEntity="Claroline\ClacoFormBundle\Entity\Category")
     * @ORM\JoinTable(name="claro_clacoformbundle_entry_category")
     * @Groups({"api_claco_form", "api_user_min"})
     * @SerializedName("categories")
     */
    protected $categories;

    /**
     * @ORM\ManyToMany(targetEntity="Claroline\ClacoFormBundle\Entity\Keyword")
     * @ORM\JoinTable(name="claro_clacoformbundle_entry_keyword")
     * @Groups({"api_claco_form", "api_user_min"})
     * @SerializedName("keywords")
     */
    protected $keywords;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\ClacoFormBundle\Entity\EntryUser",
     *     mappedBy="entry"
     * )
     */
    protected $entryUsers;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->fieldValues = new ArrayCollection();
        $this->keywords = new ArrayCollection();
        $this->entryUsers = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getClacoForm()
    {
        return $this->clacoForm;
    }

    public function setClacoForm(ClacoForm $clacoForm)
    {
        $this->clacoForm = $clacoForm;
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

    public function getPublicationDate()
    {
        return $this->publicationDate;
    }

    public function setPublicationDate(\DateTime $publicationDate = null)
    {
        $this->publicationDate = $publicationDate;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user = null)
    {
        $this->user = $user;
    }

    public function getFieldValues()
    {
        return $this->fieldValues->toArray();
    }

    public function addFieldValue(FieldValue $fieldValue)
    {
        if (!$this->fieldValues->contains($fieldValue)) {
            $this->fieldValues->add($fieldValue);
        }

        return $this;
    }

    public function removeValue(FieldValue $fieldValue)
    {
        if ($this->fieldValues->contains($fieldValue)) {
            $this->fieldValues->removeElement($fieldValue);
        }

        return $this;
    }

    public function emptyValues()
    {
        $this->fieldValues->clear();
    }

    public function getComments()
    {
        return $this->comments->toArray();
    }

    public function addComment(Comment $comment)
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
        }

        return $this;
    }

    public function removeComment(Comment $comment)
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
        }

        return $this;
    }

    public function emptyComments()
    {
        $this->comments->clear();
    }

    public function getCategories()
    {
        return $this->categories->toArray();
    }

    public function addCategory(Category $category)
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }

        return $this;
    }

    public function removeCategory(Category $category)
    {
        if ($this->categories->contains($category)) {
            $this->categories->removeElement($category);
        }

        return $this;
    }

    public function emptyCategories()
    {
        $this->categories->clear();
    }

    public function getKeywords()
    {
        return $this->keywords->toArray();
    }

    public function addKeyword(Keyword $keyword)
    {
        if (!$this->keywords->contains($keyword)) {
            $this->keywords->add($keyword);
        }

        return $this;
    }

    public function removeKeyword(Keyword $keyword)
    {
        if ($this->keywords->contains($keyword)) {
            $this->keywords->removeElement($keyword);
        }

        return $this;
    }

    public function emptyKeywords()
    {
        $this->keywords->clear();
    }

    public function getEntryUsers()
    {
        return $this->entryUsers->toArray();
    }
}
