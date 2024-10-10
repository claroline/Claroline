<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AppBundle\API;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Claroline\AppBundle\Persistence\ObjectManager;

class FinderProvider
{
    public function __construct(
        private readonly ObjectManager $om,
        private readonly SerializerProvider $serializer,
        private readonly iterable $finders
    ) {
    }

    /**
     * Gets a registered finder instance.
     */
    public function get(string $class): AbstractFinder
    {
        $finders = $this->finders instanceof \Traversable ? iterator_to_array($this->finders) : $this->finders;
        if (!isset($finders[$class])) {
            throw new FinderException(sprintf('No finder found for class "%s" Maybe you forgot to add the "claroline.finder" tag to your finder.', $class));
        }

        return $finders[$class];
    }

    /**
     * Gets all the finders defined in the app (required for test purpose).
     */
    public function all(): array
    {
        $finders = $this->finders instanceof \Traversable ? iterator_to_array($this->finders) : $this->finders;

        return array_values($finders);
    }

    /**
     * Builds and fires the query for a given class. The result will be serialized afterward.
     */
    public function search(string $class, array $finderParams = [], array $serializerOptions = []): array
    {
        $results = $this->searchEntities($class, $finderParams);

        return array_merge($results, [
            'data' => array_map(function ($result) use ($serializerOptions) {
                return $this->serializer->serialize($result, $serializerOptions);
            }, $results['data']),
        ]);
    }

    public function count(string $class, array $finderParams = []): int
    {
        $queryParams = self::parseQueryParams($finderParams);

        $page = $queryParams['page'];
        $limit = $queryParams['limit'];
        $allFilters = $queryParams['allFilters'];
        $sortBy = $queryParams['sortBy'];

        // count the total results (without pagination)
        return $this->fetch($class, $allFilters, $sortBy, $page, $limit, true);
    }

    public function searchEntities(string $class, array $finderParams = []): array
    {
        $queryParams = self::parseQueryParams($finderParams);
        $page = $queryParams['page'];
        $limit = $queryParams['limit'];
        $filters = $queryParams['filters'];
        $allFilters = $queryParams['allFilters'];
        $sortBy = $queryParams['sortBy'];
        // count the total results (without pagination)
        $count = $this->fetch($class, $allFilters, $sortBy, $page, $limit, true);
        // get the list of data for the current search and page
        $data = $this->fetch($class, $allFilters, $sortBy, $page, $limit);

        if (0 < $count && empty($data)) {
            // search should have returned results, but we have requested a non-existent page => get the last page
            $page = 0 !== $limit ? ceil($count / $limit) - 1 : 1;
            // load last page data
            $data = $this->fetch($class, $allFilters, $sortBy, $page, $limit);
        }

        return self::formatPaginatedData(
            $data,
            $count,
            $page,
            $limit,
            $filters,
            $sortBy
        );
    }

    /**
     * Builds and fires the query for a given class. There will be no serialization here.
     */
    public function fetch(string $class, ?array $filters = [], array $sortBy = null, ?int $page = 0, ?int $limit = -1, ?bool $count = false): int|array
    {
        try {
            return $this->get($class)->find($filters, $sortBy, $page, $limit, $count);
        } catch (FinderException $e) {
            if ($count) {
                return $this->om->getRepository($class)->count($filters);
            }

            return $this->om->getRepository($class)->findBy($filters, null, 0 < $limit ? $limit : null, $page);
        }
    }

    /**
     * Parses query params to their appropriate filter values.
     */
    public static function parseQueryParams(array $finderParams = []): array
    {
        $filters = isset($finderParams['filters']) ? self::parseFilters($finderParams['filters']) : [];
        $sortBy = /*isset($finderParams['sortBy']) ? self::parseSortBy($finderParams['sortBy']) :*/ null;
        $page = isset($finderParams['page']) ? (int) $finderParams['page'] : 0;
        $limit = isset($finderParams['limit']) ? (int) $finderParams['limit'] : -1;

        // these filters are not configurable/displayed in UI
        // it's mostly used for access restrictions or entity collections
        $hiddenFilters = isset($finderParams['hiddenFilters']) ? self::parseFilters($finderParams['hiddenFilters']) : [];

        return [
            'filters' => $filters,
            'hiddenFilters' => $hiddenFilters,
            'allFilters' => array_merge_recursive($filters, $hiddenFilters),
            'sortBy' => $sortBy,
            'page' => $page,
            'limit' => $limit,
        ];
    }

    private static function formatPaginatedData(array $data, int $total, int $page, int $limit, ?array $filters = [], ?array $sortBy = []): array
    {
        return [
            'data' => $data,
            'totalResults' => $total,
            'page' => $page,
            'pageSize' => $limit,
            'filters' => self::decodeFilters($filters),
            'sortBy' => $sortBy,
        ];
    }

    private static function parseSortBy(?string $sortBy): array
    {
        // default values
        $property = null;
        $direction = 0;

        if (!empty($sortBy)) {
            if ('-' === substr($sortBy, 0, 1)) {
                $property = substr($sortBy, 1);
                $direction = -1;
            } else {
                $property = $sortBy;
                $direction = 1;
            }
        }

        return [
            'property' => $property,
            'direction' => $direction,
        ];
    }

    /**
     * Properly convert the filters (boolean or integer for instance when they're displayed as string).
     */
    private static function parseFilters(array $filters): array
    {
        $parsed = [];
        foreach ($filters as $property => $value) {
            // don't keep empty filters
            if ('' !== $value) {
                if (null !== $value) {
                    // parse filter value
                    if (is_numeric($value)) {
                        // convert numbers
                        $floatValue = floatval($value);
                        if ($value === $floatValue.'') {
                            // dumb check to allow users search with strings like '001' without catching it as a number
                            $value = $floatValue;
                        }
                    } else {
                        // convert booleans
                        $booleanValue = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                        if (null !== $booleanValue) {
                            $value = $booleanValue;
                        }
                    }
                }

                $parsed[$property] = $value;
            }
        }

        return $parsed;
    }

    private static function decodeFilters(array $filters): array
    {
        $decodedFilters = [];
        foreach ($filters as $property => $value) {
            $decodedFilters[] = ['value' => $value, 'property' => $property];
        }

        return $decodedFilters;
    }
}
