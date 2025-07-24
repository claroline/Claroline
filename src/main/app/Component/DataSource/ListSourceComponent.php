<?php

namespace Claroline\AppBundle\Component\DataSource;

use Claroline\AppBundle\API\Finder\FinderFactoryInterface;
use Claroline\AppBundle\API\Finder\FinderQuery;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedJsonResponse;

abstract class ListSourceComponent extends DataSourceComponent
{
    private FinderFactoryInterface $finderFactory;
    private SerializerProvider $serializer;

    abstract public static function getClass(): string;

    public static function getType(): string
    {
        return 'list';
    }

    public function setFinder(FinderFactoryInterface $finderFactory): void
    {
        $this->finderFactory = $finderFactory;
    }

    public function setSerializer(SerializerProvider $serializer): void
    {
        $this->serializer = $serializer;
    }

    public function getQuery(string $context, ?ContextSubjectInterface $contextSubject = null, ?Request $request = null): FinderQuery
    {
        if ($request) {
            $data = $request->query->all();

            $finderQuery = new FinderQuery(
                !empty($data['q']) ? $data['q'] : null,
                !empty($data['filters']) ? $data['filters'] : [],
                !empty($data['sortBy']) ? $data['sortBy'] : [],
                !empty($data['page']) ? $data['page'] : 0,
                !empty($data['limit']) ? $data['limit'] : FinderQuery::ALL,
            );
        } else {
            $finderQuery = new FinderQuery();
        }

        if ($contextSubject) {
            $finderQuery->addFilter('workspace', $contextSubject->getContextIdentifier());
        }

        return $finderQuery;
    }

    public function open(string $context, ?ContextSubjectInterface $contextSubject = null, ?Request $request = null): StreamedJsonResponse
    {
        $options = static::getOptions();
        $finderQuery = $this->getQuery($context, $contextSubject, $request);

        return $this->finderFactory->create(static::getClass())
            ->submit($finderQuery)
            ->getResult(function (object $entity) use ($options): array {
                return $this->serializer->serialize($entity, $options);
            })
            ->toResponse()
        ;
    }

    protected static function getOptions(): array
    {
        return [SerializerInterface::SERIALIZE_LIST];
    }
}
