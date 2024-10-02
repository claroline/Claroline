<?php

namespace Claroline\AppBundle\API\Finder;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

class Finder implements FinderInterface
{
    private string $name;
    private ResolvedFinderTypeInterface $type;
    private ?FinderInterface $parent = null;
    /** @var FinderInterface[] */
    private array $children = [];
    private FinderQuery $query;
    private array $options;
    private bool $distinct = false;
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em, ResolvedFinderTypeInterface $type, string $name, array $options)
    {
        $this->name = $name;
        $this->type = $type;
        $this->options = $options;
        $this->em = $em;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAlias(): string
    {
        if (null !== $this->parent) {
            return $this->parent->getAlias().'_'.$this->getName();
        }

        return $this->getName();
    }

    public function getPropertyPath(): string
    {
        if (null !== $this->parent && !$this->parent->isRoot()) {
            return $this->parent->getPropertyPath().'.'.$this->getName();
        }

        return $this->getName();
    }

    /**
     * Get the path of the finder prop in the final DQL.
     */
    public function getQueryPath(bool $withAliases = true): string
    {
        if ($withAliases && isset($this->options['data_class'])) {
            return $this->getAlias();
        }

        if (null !== $this->parent) {
            return $this->parent->getQueryPath().'.'.$this->getName();
        }

        return $this->getName();
    }

    public function distinct(bool $flag = true): static
    {
        $this->distinct = $flag;

        return $this;
    }

    public function add(FinderInterface $child): static
    {
        $this->children[$child->getName()] = $child;

        $child->setParent($this);

        return $this;
    }

    public function has(string $name): bool
    {
        return isset($this->children[$name]);
    }

    public function get(string $name): FinderInterface
    {
        if (isset($this->children[$name])) {
            return $this->children[$name];
        }

        throw new \OutOfBoundsException(sprintf('Child "%s" does not exist.', $name));
    }

    public function submit(?FinderQuery $query): static
    {
        $this->query = $query ?? new FinderQuery();

        foreach ($this->children as $child) {
            $child->submit($this->query);
        }

        return $this;
    }

    public function getResult(?callable $rowTransformer = null): FinderResultInterface
    {
        if (!$this->isRoot()) {
            throw new \RuntimeException('Method can only be called on root finder.');
        }

        $queryBuilder = $this->createQueryBuilder();
        if (0 < $this->query->getPageSize()) {
            $queryBuilder->setFirstResult($this->query->getPage() * $this->query->getPageSize());
            $queryBuilder->setMaxResults($this->query->getPageSize());
        }

        return new FinderResult($this->getAlias(), $this->query, $queryBuilder, $rowTransformer);
    }

    public function getSearchValue(): ?string
    {
        return $this->query->getSearch();
    }

    public function getFilterValue(): mixed
    {
        return $this->query->getFilter($this->getPropertyPath());
    }

    public function getSortValue(): ?string
    {
        return $this->query->getSort($this->getPropertyPath());
    }

    public function createQueryBuilder(?QueryBuilder $queryBuilder = null): QueryBuilder
    {
        if (null === $queryBuilder && $this->parent) {
            $queryBuilder = $this->parent->createQueryBuilder();
        }

        if (null === $queryBuilder) {
            $queryBuilder = $this->em->createQueryBuilder()
                ->select($this->getAlias())
                ->from($this->options['data_class'], $this->getAlias());
        }

        $this->type->buildQuery($queryBuilder, $this, $this->options);

        if ($this->distinct) {
            // only enable distinct mode when required to increase performances if possible
            $queryBuilder->distinct();
        }

        foreach ($this->children as $child) {
            $child->createQueryBuilder($queryBuilder);
        }

        return $queryBuilder;
    }

    public function isRoot(): bool
    {
        return null === $this->parent;
    }

    public function getParent(): ?FinderInterface
    {
        return $this->parent;
    }

    private function setParent(FinderInterface $parent): static
    {
        $this->parent = $parent;

        return $this;
    }
}
