<?php

namespace Claroline\AppBundle\API\Finder;

use Symfony\Component\HttpFoundation\Request;

interface FinderInterface
{
    /**
     * Returns the local name of the property that the finder is mapped.
     * Attention : this name MAY NOT be unique in the Finder tree {@see getAlias()}.
     */
    public function getName(): string;

    /**
     * Returns a unique name for the property that the finder is mapped to.
     * It's used for relationship aliases and to bind parameters to the final QueryBuilder.
     */
    public function getAlias(): string;

    /**
     * Returns the property path that the finder is mapped to.
     */
    public function getPropertyPath(): string;

    /**
     * Get the path of the managed property in the final DQL query.
     */
    public function getQueryPath(bool $withAliases = true): string;

    /**
     * Tells the Finder if the generated query will require the DISTINCT keyword.
     * NB. It's required when using One-to-Many / Many-to-Many join which produce duplicated rows.
     */
    public function distinct(bool $flag = true): static;

    public function add(self $child): static;

    public function handleRequest(?Request $request = null): static;

    public function submit(FinderQuery $query): static;

    /**
     * Shortcut to get the current search string {@see FinderQuery}.
     */
    public function getSearchValue(): ?string;

    /**
     * Shortcut to get the filter value for current finder instance {@see FinderQuery}.
     */
    public function getFilterValue(): mixed;

    /**
     * Shortcut to get the sort value for current finder instance {@see FinderQuery}.
     */
    public function getSortValue(): ?string;

    public function getResult(?callable $rowTransformer = null): FinderResultInterface;

    public function isRoot(): bool;

    public function getParent(): ?self;
}
