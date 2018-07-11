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
use Doctrine\ORM\Query\Query;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.api.finder")
 */
class FinderProvider
{
    /** @var ObjectManager */
    private $om;
    /** @var SerializerProvider */
    private $serializer;

    /**
     * The list of registered finders in the platform.
     *
     * @var array
     */
    private $finders = [];

    /**
     * Finder constructor.
     *
     * @DI\InjectParams({
     *     "om"         = @DI\Inject("claroline.persistence.object_manager"),
     *     "serializer" = @DI\Inject("claroline.api.serializer")
     * })
     *
     * @param ObjectManager      $om
     * @param SerializerProvider $serializer
     */
    public function __construct(
        ObjectManager $om,
        SerializerProvider $serializer
    ) {
        $this->om = $om;
        $this->serializer = $serializer;
    }

    /**
     * Registers a new finder.
     *
     * @param FinderInterface $finder
     */
    public function add(AbstractFinder $finder)
    {
        $this->finders[$finder->getClass()] = $finder;
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
        if (empty($this->finders[$class])) {
            throw new FinderException(
                sprintf('No finder found for class "%s" Maybe you forgot to add the "claroline.finder" tag to your finder.', $class)
            );
        }

        return $this->finders[$class];
    }

    /**
     * Return the list of finders.
     *
     * @return mixed[];
     */
    public function all()
    {
        return $this->finders;
    }

    /**
     * Builds and fires the query for a given class. The result will be serialized afterwards.
     *
     * @param string $class
     * @param array  $finderParams
     * @param array  $serializerOptions
     *
     * @return array
     */
    public function search($class, array $finderParams = [], array $serializerOptions = [])
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
            $page = ceil($count / $limit) - 1;
            // load last page data
            $data = $this->fetch($class, $allFilters, $sortBy, $page, $limit);
        }

        return self::formatPaginatedData(
            array_map(function ($result) use ($serializerOptions) {
                return $this->serializer->serialize($result, $serializerOptions);
            }, $data),
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
     * @param string     $class
     * @param int        $page
     * @param int        $limit
     * @param array      $filters
     * @param array|null $sortBy
     * @param bool       $count
     *
     * @return mixed
     */
    public function fetch($class, array $filters = [], array $sortBy = null, $page = 0, $limit = -1, $count = false)
    {
        try {
            return $this->get($class)->find($filters, $sortBy, $page, $limit, $count);
        } catch (FinderException $e) {
            $data = $this->om->getRepository($class)->findBy($filters, null, 0 < $limit ? $limit : null, $page);

            return $count ? count($data) : $data;
        }
    }

    /**
     * @param string $sortBy
     *
     * @todo : we should make UI and API formats uniform to avoid such transformations
     *
     * @return array
     */
    public static function parseSortBy($sortBy)
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
     * @param array $filters
     *
     * @return array
     */
    public static function parseFilters(array $filters)
    {
        $parsed = [];
        foreach ($filters as $property => $value) {
            // don't keep empty filters
            if ('' !== $value) {
                if (null !== $value) {
                    // parse filter value
                    if (is_numeric($value)) {
                        // convert numbers
                        $value = floatval($value);
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
     * @param array $filters
     *
     * @todo : we should make UI and API formats uniform to avoid such transformations
     *
     * @return array
     */
    public static function decodeFilters(array $filters)
    {
        $decodedFilters = [];
        foreach ($filters as $property => $value) {
            $decodedFilters[] = ['value' => $value, 'property' => $property];
        }

        return $decodedFilters;
    }

    /**
     * Parses query params to their appropriate filter values.
     *
     * @param array $finderParams
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
}
