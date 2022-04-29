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

use Claroline\AppBundle\API\Finder\FinderInterface;
use Claroline\AppBundle\Persistence\ObjectManager;

class FinderProvider
{
    /** @var ObjectManager */
    private $om;
    /** @var SerializerProvider */
    private $serializer;
    /** @var iterable */
    private $finders;

    public function __construct(
        ObjectManager $om,
        iterable $finders,
        SerializerProvider $serializer
    ) {
        $this->om = $om;
        $this->finders = $finders;
        $this->serializer = $serializer;
    }

    /**
     * Gets a registered finder instance.
     *
     * @param string $class
     *
     * @return FinderInterface
     *
     * @throws \Exception
     */
    public function get($class)
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
     * Builds and fires the query for a given class. The result will be serialized afterwards.
     *
     * @param string $class
     *
     * @return array
     */
    public function search($class, array $finderParams = [], array $serializerOptions = [])
    {
        $results = $this->searchEntities($class, $finderParams);

        return array_merge($results, [
            'data' => array_map(function ($result) use ($serializerOptions) {
                return $this->serializer->serialize($result, $serializerOptions);
            }, $results['data']),
        ]);
    }

    /**
     * @param $class
     *
     * @return array
     */
    public function searchEntities($class, array $finderParams = [])
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
            // search should have returned results, but we have requested a non existent page => get the last page
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
     *
     * @param string $class
     * @param int    $page
     * @param int    $limit
     * @param bool   $count
     *
     * @return mixed
     */
    public function fetch($class, array $filters = [], array $sortBy = null, $page = 0, $limit = -1, $count = false)
    {
        try {
            return $this->get($class)->find($filters, $sortBy, $page, $limit, $count);
        } catch (FinderException $e) {
            if ($count) {
                return count($this->om->getRepository($class)->findBy($filters));
            }

            return $this->om->getRepository($class)->findBy($filters, null, 0 < $limit ? $limit : null, $page);
        }
    }

    /**
     * Parses query params to their appropriate filter values.
     * Should not be public. Only used by old logs queries.
     *
     * @return array
     */
    public static function parseQueryParams(array $finderParams = [])
    {
        $filters = isset($finderParams['filters']) ? self::parseFilters($finderParams['filters']) : [];
        $sortBy = isset($finderParams['sortBy']) ? self::parseSortBy($finderParams['sortBy']) : null;
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

    /**
     * Should not be public. Only used by old logs queries.
     */
    public static function formatPaginatedData($data, $total, $page, $limit, $filters, $sortBy)
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

    /**
     * @param string $sortBy
     *
     * @todo : we should make UI and API formats uniform to avoid such transformations
     *
     * @return array
     */
    private static function parseSortBy($sortBy)
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
     *
     * @return array
     */
    private static function parseFilters(array $filters)
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

    /**
     * @todo : we should make UI and API formats uniform to avoid such transformations
     *
     * @return array
     */
    private static function decodeFilters(array $filters)
    {
        $decodedFilters = [];
        foreach ($filters as $property => $value) {
            $decodedFilters[] = ['value' => $value, 'property' => $property];
        }

        return $decodedFilters;
    }
}
