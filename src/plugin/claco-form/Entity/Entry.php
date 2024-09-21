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

use Doctrine\DBAL\Types\Types;
use Claroline\ClacoFormBundle\Repository\EntryRepository;
use DateTimeInterface;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\CoreBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_clacoformbundle_entry')]
#[ORM\Entity(repositoryClass: EntryRepository::class)]
class Entry
{
    use Id;
    use Uuid;

    public const PENDING = 0;
    public const PUBLISHED = 1;
    public const UNPUBLISHED = 2;

    #[ORM\Column]
    private ?string $title = null;

    #[ORM\Column(name: 'entry_status', type: Types::INTEGER)]
    private int $status = self::PENDING;

    #[ORM\Column(name: 'locked', type: Types::BOOLEAN, options: ['default' => 0])]
    private bool $locked = false;

    
    #[ORM\JoinColumn(name: 'claco_form_id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: ClacoForm::class, inversedBy: 'categories')]
    private ?ClacoForm $clacoForm = null;

    
    #[ORM\JoinColumn(name: 'user_id', onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $user = null;

    #[ORM\Column(name: 'creation_date', type: Types::DATETIME_MUTABLE, nullable: false)]
    private ?DateTimeInterface $creationDate = null;

    #[ORM\Column(name: 'edition_date', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $editionDate = null;

    #[ORM\Column(name: 'publication_date', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $publicationDate = null;

    /**
     *
     *
     * @var FieldValue[]
     */
    #[ORM\JoinTable(name: 'claro_clacoformbundle_entry_value')]
    #[ORM\OneToMany(targetEntity: FieldValue::class, mappedBy: 'entry', cascade: ['persist'])]
    private $fieldValues;

    /**
     *
     *
     * @var Comment[]
     */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'entry')]
    #[ORM\OrderBy(['creationDate' => 'DESC'])]
    private $comments;

    /**
     *
     *
     * @var Category[]
     */
    #[ORM\JoinTable(name: 'claro_clacoformbundle_entry_category')]
    #[ORM\ManyToMany(targetEntity: Category::class)]
    private $categories;

    /**
     *
     *
     * @var Keyword[]
     */
    #[ORM\JoinTable(name: 'claro_clacoformbundle_entry_keyword')]
    #[ORM\ManyToMany(targetEntity: Keyword::class, cascade: ['persist'])]
    private $keywords;

    /**
     * @var EntryUser[]
     */
    #[ORM\OneToMany(targetEntity: EntryUser::class, mappedBy: 'entry')]
    private $entryUsers;

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

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    public function isLocked(): bool
    {
        return $this->locked;
    }

    public function setLocked(bool $locked): void
    {
        $this->locked = $locked;
    }

    public function getClacoForm(): ?ClacoForm
    {
        return $this->clacoForm;
    }

    public function setClacoForm(ClacoForm $clacoForm): void
    {
        $this->clacoForm = $clacoForm;
    }

    public function getCreationDate(): ?DateTimeInterface
    {
        return $this->creationDate;
    }

    public function setCreationDate(?DateTimeInterface $creationDate = null): void
    {
        $this->creationDate = $creationDate;
    }

    public function getEditionDate(): ?DateTimeInterface
    {
        return $this->editionDate;
    }

    public function setEditionDate(?DateTimeInterface $editionDate = null): void
    {
        $this->editionDate = $editionDate;
    }

    public function getPublicationDate(): ?DateTimeInterface
    {
        return $this->publicationDate;
    }

    public function setPublicationDate(?DateTimeInterface $publicationDate = null): void
    {
        $this->publicationDate = $publicationDate;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user = null): void
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
            if ($field->getUuid() === $fieldValue->getField()->getUuid()) {
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
