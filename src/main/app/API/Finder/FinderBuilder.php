<?php

namespace Claroline\AppBundle\API\Finder;

use BadMethodCallException;
use Claroline\AppBundle\API\Finder\Type\TextType;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;

class FinderBuilder implements FinderBuilderInterface
{
    private string $name;

    /**
     * The children of the finder builder.
     *
     * @var FinderBuilderInterface[]
     */
    private array $children = [];

    private ResolvedFinderTypeInterface $type;

    private FinderFactoryInterface $factory;

    private EntityManagerInterface $em;

    private array $options;

    private bool $locked = false;

    public function __construct(EntityManagerInterface $em, FinderFactoryInterface $factory, ResolvedFinderTypeInterface $type, string $name, array $options = [])
    {
        $this->name = $name;
        $this->type = $type;
        $this->options = $options;
        $this->factory = $factory;
        $this->em = $em;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getFinder(): FinderInterface
    {
        if ($this->locked) {
            throw new BadMethodCallException('FinderBuilder methods cannot be accessed anymore once the builder is turned into a FinderInterface instance.');
        }

        $this->locked = true;

        $finder = new Finder($this->em, $this->type, $this->name, $this->options);

        foreach ($this->children as $child) {
            $finder->add($child->getFinder());
        }

        return $finder;
    }

    public function add(string $name, ?string $type = null, ?array $options = []): static
    {
        if ($this->locked) {
            throw new BadMethodCallException('FinderBuilder methods cannot be accessed anymore once the builder is turned into a FinderInterface instance.');
        }

        if (null === $type) {
            $type = TextType::class;
        }

        $this->children[$name] = $this->factory->createNamedBuilder($name, $type, $options);

        return $this;
    }

    public function get(string $name): FinderBuilderInterface
    {
        if ($this->locked) {
            throw new BadMethodCallException('FinderBuilder methods cannot be accessed anymore once the builder is turned into a FinderInterface instance.');
        }

        if (isset($this->children[$name])) {
            return $this->children[$name];
        }

        throw new InvalidArgumentException(sprintf('The child with the name "%s" does not exist.', $name));
    }
}
