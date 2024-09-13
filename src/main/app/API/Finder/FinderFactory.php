<?php

namespace Claroline\AppBundle\API\Finder;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FinderFactory implements FinderFactoryInterface
{
    public function __construct(
        private readonly FinderRegistryInterface $registry,
        private readonly EntityManagerInterface $em
    ) {
    }

    public function create(string $type, ?array $options = []): FinderInterface
    {
        return $this->createBuilder($type, $options)->getFinder();
    }

    public function createBuilder(string $type, ?array $options = []): FinderBuilderInterface
    {
        return $this->createNamedBuilder('obj', $type, $options);
    }

    public function createNamedBuilder(string $name, string $type, ?array $options = []): FinderBuilderInterface
    {
        $type = $this->registry->getType($type);
        $resolver = $type->getOptionsResolver();;

        $builder = new FinderBuilder($this->em, $this, $type, $name, $resolver->resolve($options));

        $type->buildFinder($builder, $builder->getOptions());

        return $builder;
    }
}
