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

use Claroline\AppBundle\Entity\Parameters\ListParameters;
use Claroline\ClacoFormBundle\Repository\ClacoFormRepository;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_clacoformbundle_claco_form')]
#[ORM\Entity(repositoryClass: ClacoFormRepository::class)]
class ClacoForm extends AbstractResource
{
    // entries list configuration
    use ListParameters;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $template = null;

    /**
     * @var Collection<int, Field>
     */
    #[ORM\OneToMany(targetEntity: Field::class, mappedBy: 'clacoForm')]
    private Collection $fields;

    /**
     * @var Collection<int, Category>
     */
    #[ORM\OneToMany(targetEntity: Category::class, mappedBy: 'clacoForm')]
    private Collection $categories;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $details = [];

    /**
     * Ask for confirmation when a user submit a new entry.
     */
    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $showConfirm = false;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $confirmMessage = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $helpMessage = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $statistics = false;

    public function __construct()
    {
        parent::__construct();

        $this->categories = new ArrayCollection();
        $this->fields = new ArrayCollection();
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function setTemplate(?string $template = null): void
    {
        $this->template = $template;
    }

    /**
     * Get fields.
     *
     * @return Field[]
     */
    public function getFields(): array
    {
        return $this->fields->toArray();
    }

    public function addField(Field $field): void
    {
        if (!$this->fields->contains($field)) {
            $this->fields->add($field);
        }
    }

    public function removeField(Field $field): void
    {
        if ($this->fields->contains($field)) {
            $this->fields->removeElement($field);
        }
    }

    public function emptyFields(): void
    {
        $this->fields->clear();
    }

    public function getCategory(string $categoryId): ?Category
    {
        $found = null;

        foreach ($this->categories as $category) {
            if ($category->getUuid() === $categoryId) {
                $found = $category;
                break;
            }
        }

        return $found;
    }

    /**
     * @return Category[]
     */
    public function getCategories(): array
    {
        return $this->categories->toArray();
    }

    public function addCategory(Category $category): void
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
            $category->setClacoForm($this);
        }
    }

    public function removeCategory(Category $category): void
    {
        if ($this->categories->contains($category)) {
            $this->categories->removeElement($category);
            $category->setClacoForm(null);
        }
    }

    public function getShowConfirm(): bool
    {
        return $this->showConfirm;
    }

    public function setShowConfirm(bool $showConfirm): void
    {
        $this->showConfirm = $showConfirm;
    }

    public function getConfirmMessage(): ?string
    {
        return $this->confirmMessage;
    }

    public function setConfirmMessage(?string $message = null): void
    {
        $this->confirmMessage = $message;
    }

    public function getHelpMessage(): ?string
    {
        return $this->helpMessage;
    }

    public function setHelpMessage(?string $message = null): void
    {
        $this->helpMessage = $message;
    }

    public function getDetails(): array
    {
        return $this->details ?? [];
    }

    public function setDetails($details): void
    {
        $this->details = $details;
    }

    public function getMaxEntries(): int
    {
        return !is_null($this->details) && isset($this->details['max_entries']) ? $this->details['max_entries'] : 0;
    }

    public function setMaxEntries($maxEntries): void
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['max_entries'] = $maxEntries;
    }

    public function isEditionEnabled(): bool
    {
        return !is_null($this->details) && isset($this->details['edition_enabled']) ? $this->details['edition_enabled'] : true;
    }

    public function setEditionEnabled($editionEnabled): void
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['edition_enabled'] = $editionEnabled;
    }

    public function isModerated(): bool
    {
        return !is_null($this->details) && isset($this->details['moderated']) ? $this->details['moderated'] : false;
    }

    public function setModerated($moderated): void
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['moderated'] = $moderated;
    }

    public function getDefaultHome(): string
    {
        return !is_null($this->details) && isset($this->details['default_home']) ? $this->details['default_home'] : 'menu';
    }

    public function setDefaultHome($defaultHome): void
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['default_home'] = $defaultHome;
    }

    public function getMenuPosition(): string
    {
        return !is_null($this->details) && isset($this->details['menu_position']) ? $this->details['menu_position'] : 'down';
    }

    public function setMenuPosition(?string $menuPosition): void
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['menu_position'] = $menuPosition;
    }

    public function isRandomEnabled(): bool
    {
        return !is_null($this->details) && isset($this->details['random_enabled']) ? $this->details['random_enabled'] : false;
    }

    public function setRandomEnabled(bool $randomEnabled): void
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['random_enabled'] = $randomEnabled;
    }

    public function getRandomCategories(): array
    {
        return !is_null($this->details) && isset($this->details['random_categories']) ? $this->details['random_categories'] : [];
    }

    public function setRandomCategories(array $categories): void
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['random_categories'] = $categories;
    }

    public function getRandomStartDate(): ?\DateTimeInterface
    {
        return !is_null($this->details) && isset($this->details['random_start_date']) ?
            new \DateTime($this->details['random_start_date']) :
            null;
    }

    public function setRandomStartDate(?\DateTime $startDate = null): void
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['random_start_date'] = !is_null($startDate) ? $startDate->format('Y-m-d') : null;
    }

    public function getRandomEndDate(): ?\DateTimeInterface
    {
        return !is_null($this->details) && isset($this->details['random_end_date']) ?
            new \DateTime($this->details['random_end_date']) :
            null;
    }

    public function setRandomEndDate(?\DateTime $endDate = null): void
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['random_end_date'] = !is_null($endDate) ? $endDate->format('Y-m-d') : null;
    }

    public function getSearchEnabled(): bool
    {
        return !is_null($this->details) && isset($this->details['search_enabled']) ? $this->details['search_enabled'] : true;
    }

    public function setSearchEnabled($searchEnabled): void
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['search_enabled'] = $searchEnabled;
    }

    public function isSearchColumnEnabled(): bool
    {
        return !is_null($this->details) && isset($this->details['search_column_enabled']) ? $this->details['search_column_enabled'] : true;
    }

    public function setSearchColumnEnabled($searchColumnEnabled): void
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['search_column_enabled'] = $searchColumnEnabled;
    }

    public function getSearchColumns()
    {
        return !is_null($this->details) && isset($this->details['search_columns']) ?
            $this->details['search_columns'] :
            ['title', 'date', 'user', 'categories'];
    }

    public function setSearchColumns(array $searchColumns): void
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['search_columns'] = $searchColumns;
    }

    public function getDisplayMetadata()
    {
        return !is_null($this->details) && isset($this->details['display_metadata']) ? $this->details['display_metadata'] : 'none';
    }

    public function setDisplayMetadata($displayMetadata): void
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['display_metadata'] = $displayMetadata;
    }

    public function getDisplayCategories()
    {
        return !is_null($this->details) && isset($this->details['display_categories']) ? $this->details['display_categories'] : false;
    }

    public function setDisplayCategories($displayCategories): void
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['display_categories'] = $displayCategories;
    }

    public function getUseTemplate()
    {
        return !is_null($this->details) && isset($this->details['use_template']) ? $this->details['use_template'] : false;
    }

    public function setUseTemplate($useTemplate): void
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['use_template'] = $useTemplate;
    }

    public function getDefaultDisplayMode()
    {
        return !is_null($this->details) && isset($this->details['default_display_mode']) ? $this->details['default_display_mode'] : 'table';
    }

    public function setDefaultDisplayMode($defaultDisplayMode): void
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['default_display_mode'] = $defaultDisplayMode;
    }

    public function getDisplayTitle()
    {
        return !is_null($this->details) && isset($this->details['display_title']) ? $this->details['display_title'] : 'title';
    }

    public function setDisplayTitle($displayTitle): void
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['display_title'] = $displayTitle;
    }

    public function getDisplaySubtitle()
    {
        return !is_null($this->details) && isset($this->details['display_subtitle']) ? $this->details['display_subtitle'] : 'title';
    }

    public function setDisplaySubtitle($displaySubtitle): void
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['display_subtitle'] = $displaySubtitle;
    }

    public function getDisplayContent()
    {
        return !is_null($this->details) && isset($this->details['display_content']) ? $this->details['display_content'] : 'title';
    }

    public function setDisplayContent($displayContent): void
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['display_content'] = $displayContent;
    }

    public function getTitleFieldLabel()
    {
        return !is_null($this->details) && isset($this->details['title_field_label']) ?
            $this->details['title_field_label'] :
            null;
    }

    public function setTitleFieldLabel($titleFieldLabel): void
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['title_field_label'] = $titleFieldLabel;
    }

    public function isSearchRestricted(): bool
    {
        return !is_null($this->details) && isset($this->details['search_restricted']) ? $this->details['search_restricted'] : false;
    }

    public function setSearchRestricted($searchRestricted): void
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['search_restricted'] = $searchRestricted;
    }

    public function getSearchRestrictedColumns()
    {
        return !is_null($this->details) && isset($this->details['search_restricted_columns']) ?
            $this->details['search_restricted_columns'] :
            ['title'];
    }

    public function setSearchRestrictedColumns(array $searchRestrictedColumns): void
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['search_restricted_columns'] = $searchRestrictedColumns;
    }

    public function hasStatistics(): bool
    {
        return $this->statistics;
    }

    public function setStatistics(bool $statistics): void
    {
        $this->statistics = $statistics;
    }
}
