<?php

namespace Claroline\TransferBundle\Transfer\Exporter;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\SerializerProvider;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractListExporter extends AbstractExporter
{
    /** @var TranslatorInterface */
    protected $translator;
    /** @var FinderProvider */
    protected $finder;
    /** @var SerializerProvider */
    protected $serializer;

    abstract protected static function getClass(): string;

    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

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
            'filters' => [],
            'hiddenFilters' => $this->getHiddenFilters(),
            'page' => $batchNumber,
            'limit' => $this->getBatchSize(),
        ];

        if (!empty($extra)) {
            if (!empty($extra['workspace'])) {
                $query['hiddenFilters']['workspace'] = $extra['workspace']['id'];
            }

            if (!empty($extra['filters'])) {
                foreach ($extra['filters'] as $filter) {
                    $query['filters'][$filter['property']] = $filter['value'];
                }
            }

            if (!empty($extra['sortBy'])) {
                $query['sortBy'] = $extra['sortBy']['property'];
                if (isset($extra['sortBy']['direction']) && -1 === $extra['sortBy']['direction']) {
                    $query['sortBy'] = '-'.$query['sortBy'];
                }
            }
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

    /**
     * Defines the props that can be used to filter the exported data.
     * Each filter requires `name`, `label` and `type` props.
     */
    protected function getAvailableFilters(): array
    {
        return [];
    }

    /**
     * Defines the props that can be used to sort the exported data.
     * Each filter requires `name` and `label` props.
     */
    protected function getAvailableSortBy(): array
    {
        return [];
    }

    public function getSchema(?array $options = [], ?array $extra = []): array
    {
        return [
            'properties' => [],
        ];
    }

    public function getExtraDefinition(?array $options = [], ?array $extra = []): array
    {
        $fields = [];

        // Allow the user to add filters to its export
        if (!empty($this->getAvailableFilters())) {
            $fields[] = [
                'name' => 'filters',
                'label' => $this->translator->trans('filters', [], 'platform'),
                'type' => 'collection',
                'options' => [
                    'type' => 'filter',
                    'options' => [
                        'properties' => $this->getAvailableFilters(),
                    ],
                    'placeholder' => $this->translator->trans('no_filter', [], 'platform'),
                    'button' => $this->translator->trans('add_filter', [], 'platform'),
                ],
            ];
        }

        // Allow the user to sort the exported data
        if (!empty($this->getAvailableSortBy())) {
            $sortColumns = [];
            foreach ($this->getAvailableSortBy() as $sortColumn) {
                $sortColumns[$sortColumn['name']] = $sortColumn['label'];
            }

            $fields[] = [
                'name' => 'sortBy.property',
                'label' => $this->translator->trans('sorting', [], 'platform'),
                'type' => 'choice',
                'options' => [
                    'condensed' => true,
                    'choices' => $sortColumns,
                ],
                'linked' => [
                    [
                        'name' => 'sortBy.direction',
                        'type' => 'choice',
                        'options' => [
                            'choices' => [
                                1 => $this->translator->trans('sort_asc', [], 'platform'),
                                -1 => $this->translator->trans('sort_desc', [], 'platform'),
                            ],
                        ],
                    ],
                ],
            ];
        }

        return [
            'fields' => $fields,
        ];
    }
}
