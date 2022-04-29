<?php

namespace Claroline\TransferBundle\Transfer\Exporter;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\SerializerProvider;

abstract class AbstractListExporter extends AbstractExporter
{
    /** @var FinderProvider */
    protected $finder;
    /** @var SerializerProvider */
    protected $serializer;

    abstract protected static function getClass(): string;

    public function setFinder(FinderProvider $finder)
    {
        $this->finder = $finder;
    }

    public function setSerializer(SerializerProvider $serializer)
    {
        $this->serializer = $serializer;
    }

    public function execute(int $batchNumber, ?array $options = [], ?array $extra = []): array
    {
        $query = [
            'hiddenFilters' => $this->getHiddenFilters(),
            'page' => $batchNumber,
            'limit' => $this->getBatchSize(),
        ];

        if (!empty($extra) && !empty($extra['workspace'])) {
            $query['hiddenFilters']['workspace'] = $extra['workspace']['id'];
        }

        $queryParams = FinderProvider::parseQueryParams($query);
        $page = $queryParams['page'];
        $limit = $queryParams['limit'];
        $allFilters = $queryParams['allFilters'];
        $sortBy = $queryParams['sortBy'];

        // get the list of data for the current search and page
        $data = $this->finder->fetch(static::getClass(), $allFilters, $sortBy, $page, $limit);

        return array_map(function ($entity) {
            return $this->serializer->serialize($entity, array_merge([SerializerInterface::SERIALIZE_TRANSFER], $this->getOptions()));
        }, $data);
    }

    protected function getOptions(): array
    {
        return [];
    }

    protected function getHiddenFilters(): array
    {
        return [];
    }
}
