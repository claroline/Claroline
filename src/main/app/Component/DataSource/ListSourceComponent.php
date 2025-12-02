<?php

namespace Claroline\AppBundle\Component\DataSource;

use Claroline\AppBundle\API\Finder\FinderFactoryInterface;
use Claroline\AppBundle\API\Finder\FinderRequest;
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

    public function open(string $context, ?ContextSubjectInterface $contextSubject = null, ?Request $request = null): StreamedJsonResponse
    {
        $options = static::getOptions();
        $finderRequest = $this->getRequest($context, $contextSubject, $request);

        return $this->finderFactory->create(static::getClass())
            ->submit($finderRequest)
            ->getResult(function (object $entity) use ($options): array {
                return $this->serializer->serialize($entity, $options);
            })
            ->toResponse()
        ;
    }

    protected function getRequest(string $context, ?ContextSubjectInterface $contextSubject = null, ?Request $request = null): FinderRequest
    {
        $finderRequest = $this->parseRequest($request);

        if ($contextSubject) {
            $finderRequest->addFilter('workspace', $contextSubject->getContextIdentifier());
        }

        return $finderRequest;
    }

    protected function parseRequest(?Request $request = null): FinderRequest
    {
        if ($request) {
            $data = $request->query->all();

            $finderRequest = new FinderRequest(
                !empty($data['q']) ? $data['q'] : null,
                !empty($data['filters']) ? $data['filters'] : [],
                !empty($data['sortBy']) ? $data['sortBy'] : [],
                !empty($data['page']) ? $data['page'] : 0,
                !empty($data['limit']) ? $data['limit'] : FinderRequest::ALL,
            );
        } else {
            $finderRequest = new FinderRequest();
        }

        return $finderRequest;
    }

    protected static function getOptions(): array
    {
        return [SerializerInterface::SERIALIZE_LIST];
    }
}
