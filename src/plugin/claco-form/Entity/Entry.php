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

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\CoreBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Claroline\ClacoFormBundle\Repository\EntryRepository")
 * @ORM\Table(name="claro_clacoformbundle_entry")
 */
class Entry
{
    use Id;
    use Uuid;

    const PENDING = 0;
    const PUBLISHED = 1;
    const UNPUBLISHED = 2;

    /**
     * @ORM\Column
     * @Assert\NotBlank()
     *
     * @var string
     */
    protected $title;

    /**
     * @ORM\Column(name="entry_status", type="integer")
     *
     * @var int
     */
    protected $status;

    /**
     * @ORM\Column(name="locked", type="boolean", options={"default" = 0})
     *
     * @var bool
     */
    protected $locked = false;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\ClacoFormBundle\Entity\ClacoForm",
     *     inversedBy="categories"
     * )
     * @ORM\JoinColumn(name="claco_form_id", nullable=false, onDelete="CASCADE")
     *
     * @var ClacoForm
     */
    protected $clacoForm;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", onDelete="SET NULL")
     *
     * @var User
     */
    protected $user;

    /**
     * @ORM\Column(name="creation_date", type="datetime", nullable=false)
     *
     * @var \DateTime
     */
    protected $creationDate;

    /**
     * @ORM\Column(name="edition_date", type="datetime", nullable=true)
     *
     * @var \DateTime
     */
    protected $editionDate = null;

    /**
     * @ORM\Column(name="publication_date", type="datetime", nullable=true)
     *
     * @var \DateTime
     */
    protected $publicationDate = null;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\ClacoFormBundle\Entity\FieldValue",
     *     mappedBy="entry",
     *     cascade={"persist"}
     * )
     * @ORM\JoinTable(name="claro_clacoformbundle_entry_value")
     *
     * @var FieldValue[]
     */
    protected $fieldValues;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\ClacoFormBundle\Entity\Comment",
     *     mappedBy="entry"
     * )
     * @ORM\OrderBy({"creationDate" = "DESC"})
     *
     * @var Comment[]
     */
    protected $comments;

    /**
     * @ORM\ManyToMany(targetEntity="Claroline\ClacoFormBundle\Entity\Category")
     * @ORM\JoinTable(name="claro_clacoformbundle_entry_category")
     *
     * @var Category[]
     */
    protected $categories;

    /**
     * @ORM\ManyToMany(targetEntity="Claroline\ClacoFormBundle\Entity\Keyword", cascade={"persist"})
     * @ORM\JoinTable(name="claro_clacoformbundle_entry_keyword")
     *
     * @var Keyword[]
     */
    protected $keywords;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\ClacoFormBundle\Entity\EntryUser",
     *     mappedBy="entry"
     * )
     *
     * @var EntryUser[]
     */
    protected $entryUsers;

    /**
     * Entry constructor.
     */
    public function __construct()
    {
        $this->refreshUuid();

        $this->categories = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->fieldValues = new ArrayCollection();
        $this->keywords = new ArrayCollection();
        $this->entryUsers = new ArrayCollection();
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set title.
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
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

    /**
     * Is locked ?
     *
     * @return bool
     */
    public function isLocked()
    {
        return $this->locked;
    }

    /**
     * Set locked.
     *
     * @param bool $locked
     */
    public function setLocked($locked)
    {
        $this->locked = $locked;
    }

    /**
     * Get claco form.
     *
     * @return ClacoForm
     */
    public function getClacoForm()
    {
        return $this->clacoForm;
    }

    /**
     * Set claco form.
     */
    public function setClacoForm(ClacoForm $clacoForm)
    {
        $this->clacoForm = $clacoForm;
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
     */
    public function setEditionDate(\DateTime $editionDate = null)
    {
        $this->editionDate = $editionDate;
    }

    /**
     * Get publication date.
     *
     * @return \DateTime
     */
    public function getPublicationDate()
    {
        return $this->publicationDate;
    }

    /**
     * Set publication date.
     */
    public function setPublicationDate(\DateTime $publicationDate = null)
    {
        $this->publicationDate = $publicationDate;
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
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;
    }

    /**
     * Gert field values.
     *
     * @return FieldValue[]
     */
    public function getFieldValues()
    {
        return $this->fieldValues->toArray();
    }

    public function getFieldValue(Field $field): ?FieldValue
    {
        $value = null;

        foreach ($this->fieldValues as $fieldValue) {
            if ($field->getId() === $fieldValue->getField()->getId()) {
                $value = $fieldValue;
                break;
            }
        }

        return $value;
    }

    /**
     * Add a field value.
     */
    public function addFieldValue(FieldValue $fieldValue)
    {
        if (!$this->fieldValues->contains($fieldValue)) {
            $this->fieldValues->add($fieldValue);
        }
    }

    /**
     * Remove a field value.
     */
    public function removeValue(FieldValue $fieldValue)
    {
        if ($this->fieldValues->contains($fieldValue)) {
            $this->fieldValues->removeElement($fieldValue);
        }
    }

    /**
     * Removes all field values.
     */
    public function emptyValues()
    {
        $this->fieldValues->clear();
    }

    /**
     * Get comments.
     *
     * @return Comment[]
     */
    public function getComments()
    {
        return $this->comments->toArray();
    }

    /**
     * Add comment.
     */
    public function addComment(Comment $comment)
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
        }
    }

    /**
     * Remove comment.
     */
    public function removeComment(Comment $comment)
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
        }
    }

    /**
     * Remove all comments.
     */
    public function emptyComments()
    {
        $this->comments->clear();
    }

    /**
     * Get categories.
     *
     * @return Category[]
     */
    public function getCategories()
    {
        return $this->categories->toArray();
    }

    public function hasCategory(Category $category)
    {
        return $this->categories->contains($category);
    }

    /**
     * Add category.
     */
    public function addCategory(Category $category)
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }
    }

    /**
     * Remove category.
     */
    public function removeCategory(Category $category)
    {
        if ($this->categories->contains($category)) {
            $this->categories->removeElement($category);
        }
    }

    /**
     * Removes all categories.
     */
    public function emptyCategories()
    {
        $this->categories->clear();
    }

    /**
     * Get keywords.
     *
     * @return Keyword[]
     */
    public function getKeywords()
    {
        return $this->keywords->toArray();
    }

    /**
     * Add keyword.
     */
    public function addKeyword(Keyword $keyword)
    {
        if (!$this->keywords->contains($keyword)) {
            $this->keywords->add($keyword);
        }
    }

    /**
     * Remove keyword.
     */
    public function removeKeyword(Keyword $keyword)
    {
        if ($this->keywords->contains($keyword)) {
            $this->keywords->removeElement($keyword);
        }
    }

    /**
     * Remove all keywords.
     */
    public function emptyKeywords()
    {
        $this->keywords->clear();
    }

    /**
     * Get entry users.
     *
     * @return EntryUser[]
     */
    public function getEntryUsers()
    {
        return $this->entryUsers->toArray();
    }
}
