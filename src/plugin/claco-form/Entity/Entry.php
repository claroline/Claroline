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

use Claroline\AppBundle\API\Attribute\CrudEntity;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\ClacoFormBundle\Finder\EntryType;
use Claroline\ClacoFormBundle\Repository\EntryRepository;
use Claroline\CoreBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_clacoformbundle_entry')]
#[ORM\Entity(repositoryClass: EntryRepository::class)]
#[CrudEntity(finderClass: EntryType::class)]
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

    #[ORM\ManyToOne(targetEntity: ClacoForm::class, inversedBy: 'categories')]
    #[ORM\JoinColumn(name: 'claco_form_id', nullable: false, onDelete: 'CASCADE')]
    private ?ClacoForm $clacoForm = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', onDelete: 'SET NULL')]
    private ?User $user = null;

    #[ORM\Column(name: 'creation_date', type: Types::DATETIME_MUTABLE, nullable: false)]
    private ?\DateTimeInterface $creationDate = null;

    #[ORM\Column(name: 'edition_date', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $editionDate = null;

    #[ORM\Column(name: 'publication_date', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $publicationDate = null;

    /**
     * @var Collection<int, FieldValue>
     */
    #[ORM\OneToMany(targetEntity: FieldValue::class, mappedBy: 'entry', cascade: ['persist'])]
    #[ORM\JoinTable(name: 'claro_clacoformbundle_entry_value')]
    private Collection $fieldValues;

    /**
     * @var Collection<int, Category>
     */
    #[ORM\JoinTable(name: 'claro_clacoformbundle_entry_category')]
    #[ORM\ManyToMany(targetEntity: Category::class)]
    private Collection $categories;

    public function __construct()
    {
        $this->refreshUuid();

        $this->categories = new ArrayCollection();
        $this->fieldValues = new ArrayCollection();
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
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

    public function getCreationDate(): ?\DateTimeInterface
    {
        return $this->creationDate;
    }

    public function setCreationDate(?\DateTimeInterface $creationDate = null): void
    {
        $this->creationDate = $creationDate;
    }

    public function getEditionDate(): ?\DateTimeInterface
    {
        return $this->editionDate;
    }

    public function setEditionDate(?\DateTimeInterface $editionDate = null): void
    {
        $this->editionDate = $editionDate;
    }

    public function getPublicationDate(): ?\DateTimeInterface
    {
        return $this->publicationDate;
    }

    public function setPublicationDate(?\DateTimeInterface $publicationDate = null): void
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
    public function getFieldValues(): array
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

    public function addFieldValue(FieldValue $fieldValue): void
    {
        if (!$this->fieldValues->contains($fieldValue)) {
            $this->fieldValues->add($fieldValue);
        }
    }

    public function removeValue(FieldValue $fieldValue): void
    {
        if ($this->fieldValues->contains($fieldValue)) {
            $this->fieldValues->removeElement($fieldValue);
        }
    }

    /**
     * Get categories.
     *
     * @return Category[]
     */
    public function getCategories(): array
    {
        return $this->categories->toArray();
    }

    public function hasCategory(Category $category): bool
    {
        return $this->categories->contains($category);
    }

    public function addCategory(Category $category): void
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }
    }

    public function removeCategory(Category $category): void
    {
        if ($this->categories->contains($category)) {
            $this->categories->removeElement($category);
        }
    }

    /**
     * Removes all categories.
     */
    public function emptyCategories(): void
    {
        $this->categories->clear();
    }
}
