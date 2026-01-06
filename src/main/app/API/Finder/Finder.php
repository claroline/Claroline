<?php

namespace Claroline\AppBundle\API\Finder;

use Claroline\AppBundle\Event\Finder\BuildQueryEvent;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class Finder implements FinderInterface
{
    private string $name;
    private ResolvedFinderTypeInterface $type;
    private ?FinderInterface $parent = null;
    /** @var FinderInterface[] */
    private array $children = [];
    private array $requestTransformers;
    private FinderRequest $request;
    private bool $submitted = false;
    private mixed $filterValue = null;
    private ?string $sortValue = null;
    private array $options;
    private bool $distinct = false;
    private EntityManagerInterface $em;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EntityManagerInterface $em, EventDispatcherInterface $eventDispatcher, ResolvedFinderTypeInterface $type, string $name, array $options, ?array $requestTransformers = [])
    {
        $this->name = $name;
        $this->type = $type;
        $this->options = $options;
        $this->requestTransformers = $requestTransformers;
        $this->em = $em;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): FinderTypeInterface
    {
        return $this->type->getInnerType();
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getAlias(): string
    {
        $sanitizedName = str_replace('-', '', $this->getName());
        $sanitizedName = str_replace('.', '', $sanitizedName);

        if (null !== $this->parent) {
            return $this->parent->getAlias().'_'.$sanitizedName;
        }

        return $sanitizedName;
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

    public function all(): array
    {
        return $this->children;
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

    public function submit(?FinderRequest $request): static
    {
        if (empty($request)) {
            $request = new FinderRequest();
        } else {
            $request = clone $request;
        }

        foreach ($this->requestTransformers as $transformer) {
            $request = $transformer($request, $this, $this->options);
        }

        $this->request = $request;
        $this->submitted = true;

        $this->filterValue = $this->type->submit($request->getFilter($this->getPropertyPath()), $this->options);
        $this->sortValue = $request->getSort($this->getPropertyPath());

        if (null === $this->filterValue) {
            foreach ($this->children as $child) {
                $child->submit($request);
            }
        }

        return $this;
    }

    public function getResult(?callable $rowTransformer = null, bool $readonly = true): FinderResultInterface
    {
        if (!$this->isRoot()) {
            throw new \RuntimeException('Method can only be called on root finder.');
        }

        if (!$this->submitted) {
            throw new \RuntimeException('A FinderRequest must be submitted first.');
        }

        $queryBuilder = $this->createQueryBuilder();

        return new FinderResult($this->getAlias(), $this->request, $queryBuilder, $rowTransformer, $readonly);
    }

    public function getSearchValue(): ?string
    {
        return $this->request->getSearch();
    }

    public function hasFilter(): bool
    {
        return null !== $this->filterValue;
    }

    public function getFilterValue(): mixed
    {
        return $this->filterValue;
    }

    public function getSortValue(): ?string
    {
        return $this->sortValue;
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

        $event = new BuildQueryEvent($queryBuilder, $this, $this->options);
        $this->eventDispatcher->dispatch($event);

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

    public function setParent(FinderInterface $parent): static
    {
        $this->parent = $parent;

        return $this;
    }
}
