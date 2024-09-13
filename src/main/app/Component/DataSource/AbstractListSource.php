<?php

namespace Claroline\AppBundle\Component\DataSource;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\Component\Context\ContextSubjectInterface;

abstract class AbstractListSource extends AbstractDataSource
{
    private FinderProvider $finder;

    abstract public static function getClass(): string;

    public function setFinder(FinderProvider $finder): void
    {
        $this->finder = $finder;
    }

    public function open(string $context, ContextSubjectInterface $contextSubject = null): array
    {
        return $this->finder->search(static::getClass(), static::getOptions());
    }

    protected static function getOptions(): array
    {
        return [SerializerInterface::SERIALIZE_LIST];
    }
}
