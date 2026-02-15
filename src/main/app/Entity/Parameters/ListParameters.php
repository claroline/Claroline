<?php

namespace Claroline\AppBundle\Entity\Parameters;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Contains all properties required to configure a UI ListData.
 *
 * NB. maybe create an entity with this and create a rel in the entities using it.
 */
trait ListParameters
{
    #[ORM\Column(type: Types::BOOLEAN)]
    protected bool $filterable = false;

    #[ORM\Column(type: Types::BOOLEAN)]
    protected bool $sortable = false;

    #[ORM\Column(type: Types::BOOLEAN)]
    protected bool $paginated = true;

    #[ORM\Column(type: Types::BOOLEAN)]
    protected bool $columnsFilterable = false;

    #[ORM\Column(type: Types::BOOLEAN)]
    protected bool $count = false;

    #[ORM\Column(type: Types::BOOLEAN)]
    protected bool $actions = true;

    #[ORM\Column(nullable: true)]
    protected ?string $sortBy = null;

    #[ORM\Column(type: Types::JSON)]
    protected ?array $availableSort = [];

    #[ORM\Column(type: Types::INTEGER)]
    protected int $pageSize = 15;

    #[ORM\Column(type: Types::JSON)]
    protected array $availablePageSizes = [15, 30, 60, 120, -1];

    #[ORM\Column]
    protected string $display = 'list';

    #[ORM\Column(type: Types::JSON)]
    protected array $availableDisplays = ['list'];

    #[ORM\Column(nullable: true)]
    protected ?string $searchMode = null;

    #[ORM\Column(type: Types::JSON)]
    protected array $filters = [];

    #[ORM\Column(type: Types::JSON)]
    protected array $availableFilters = [];

    #[ORM\Column(type: Types::JSON)]
    protected array $availableColumns = [];

    #[ORM\Column(type: Types::JSON)]
    protected array $displayedColumns = [];

    /**
     * The configuration of the card.
     */
    #[ORM\Column(type: Types::JSON)]
    protected array $card = [
        'icon',
        'flags',
        'subtitle',
        'description',
        'footer',
    ];

    public function isFilterable(): bool
    {
        return $this->filterable;
    }

    public function setFilterable(bool $filterable): void
    {
        $this->filterable = $filterable;
    }

    public function isSortable(): bool
    {
        return $this->sortable;
    }

    public function setSortable(bool $sortable): void
    {
        $this->sortable = $sortable;
    }

    public function isPaginated(): bool
    {
        return $this->paginated;
    }

    public function setPaginated(bool $paginated): void
    {
        $this->paginated = $paginated;
    }

    public function isColumnsFilterable(): bool
    {
        return $this->columnsFilterable;
    }

    public function setColumnsFilterable(bool $columnsFilterable): void
    {
        $this->columnsFilterable = $columnsFilterable;
    }

    public function hasCount(): bool
    {
        return $this->count;
    }

    public function setCount(bool $count): void
    {
        $this->count = $count;
    }

    public function hasActions(): bool
    {
        return $this->actions;
    }

    public function setActions(bool $actions): void
    {
        $this->actions = $actions;
    }

    public function getSortBy(): ?string
    {
        return $this->sortBy;
    }

    public function setSortBy(?string $sortBy = null): void
    {
        $this->sortBy = $sortBy;
    }

    public function getAvailableSort(): array
    {
        return $this->availableSort ?? [];
    }

    public function setAvailableSort(array $availableSort): void
    {
        $this->availableSort = $availableSort;
    }

    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    public function setPageSize(int $pageSize): void
    {
        $this->pageSize = $pageSize;
    }

    public function getAvailablePageSizes(): array
    {
        return $this->availablePageSizes ?? [];
    }

    public function setAvailablePageSizes(array $availablePageSizes): void
    {
        $this->availablePageSizes = $availablePageSizes;
    }

    public function getDisplay(): string
    {
        return $this->display;
    }

    public function setDisplay(string $display): void
    {
        $this->display = $display;
    }

    public function getAvailableDisplays(): array
    {
        return $this->availableDisplays ?? [];
    }

    public function setAvailableDisplays(array $availableDisplays): void
    {
        $this->availableDisplays = $availableDisplays;
    }

    public function getSearchMode(): ?string
    {
        return $this->searchMode;
    }

    public function setSearchMode(?string $searchMode = null): void
    {
        $this->searchMode = $searchMode;
    }

    public function getFilters(): array
    {
        return $this->filters ?? [];
    }

    public function setFilters(array $filters): void
    {
        $this->filters = $filters;
    }

    public function getAvailableFilters(): array
    {
        return $this->availableFilters ?? [];
    }

    public function setAvailableFilters(array $availableFilters): void
    {
        $this->availableFilters = $availableFilters;
    }

    public function getAvailableColumns(): array
    {
        return $this->availableColumns ?? [];
    }

    public function setAvailableColumns(array $availableColumns): void
    {
        $this->availableColumns = $availableColumns;
    }

    public function getDisplayedColumns(): array
    {
        return $this->displayedColumns ?? [];
    }

    public function setDisplayedColumns(array $displayedColumns): void
    {
        $this->displayedColumns = $displayedColumns;
    }

    public function getCard(): array
    {
        return $this->card ?? [];
    }

    public function setCard(array $card): void
    {
        $this->card = $card;
    }
}
