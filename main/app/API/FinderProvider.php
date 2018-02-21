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

use Claroline\AppBundle\Persistence\ObjectManager;
use Doctrine\ORM\QueryBuilder;
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
    public function add(FinderInterface $finder)
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
        // get search params
        $filters = isset($finderParams['filters']) ? $this->parseFilters($finderParams['filters']) : [];
        $sortBy = isset($finderParams['sortBy']) ? $this->parseSortBy($finderParams['sortBy']) : null;
        $page = isset($finderParams['page']) ? (int) $finderParams['page'] : 0;
        $limit = isset($finderParams['limit']) ? (int) $finderParams['limit'] : -1;

        // these filters are not configurable/displayed in UI
        // it's mostly used for access restrictions or entity collections
        $hiddenFilters = isset($finderParams['hiddenFilters']) ? $this->parseFilters($finderParams['hiddenFilters']) : [];

        $queryFilters = array_merge_recursive($filters, $hiddenFilters);

        // count the total results (without pagination)
        $count = $this->fetch($class, $page, $limit, $queryFilters, $sortBy, true);
        // get the list of data for the current search and page
        $data = $this->fetch($class, $page, $limit, $queryFilters, $sortBy);

        if (0 < $count && empty($data)) {
            // search should have returned results, but we have requested a non existent page => get the last page
            $page = ceil($count / $limit) - 1;
            // load last page data
            $data = $this->fetch($class, $page, $limit, $queryFilters, $sortBy);
        }

        return [
            'data' => array_map(function ($result) use ($serializerOptions) {
                return $this->serializer->serialize($result, $serializerOptions);
            }, $data),
            'totalResults' => $count,
            'page' => $page,
            'pageSize' => $limit,
            'filters' => $this->decodeFilters($filters),
            'sortBy' => $sortBy,
        ];
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
    public function fetch($class, $page, $limit, array $filters, array $sortBy = null, $count = false)
    {
        try {
            /** @var QueryBuilder $qb */
            $qb = $this->om->createQueryBuilder();

            $qb->select($count ? 'COUNT(DISTINCT obj)' : 'DISTINCT obj')
               ->from($class, 'obj');

            // filter query - let's the finder implementation process the filters to configure query
            $this->get($class)->configureQueryBuilder($qb, $filters, $sortBy);

            // order query if implementation has not done it
            $this->sortResults($qb, $sortBy);

            if (!$count && 0 < $limit) {
                $qb->setFirstResult($page * $limit);
                $qb->setMaxResults($limit);
            }

            $query = $qb->getQuery();

            return $count ? (int) $query->getSingleScalarResult() : $query->getResult();
        } catch (FinderException $e) {
            $data = $this->om->getRepository($class)->findBy($filters, null, 0 < $limit ? $limit : null, $page);

            return $count ? count($data) : $data;
        }
    }

    /**
     * @param QueryBuilder $qb
     * @param array|null   $sortBy
     */
    private function sortResults(QueryBuilder $qb, array $sortBy = null)
    {
        if (!empty($sortBy) && !empty($sortBy['property']) && 0 !== $sortBy['direction']) {
            // query needs to be sorted, check if the Finder implementation has a custom sort system
            $queryOrder = $qb->getDQLPart('orderBy');
            if (empty($queryOrder)) {
                // no order by defined
                $qb->orderBy('obj.'.$sortBy['property'], 1 === $sortBy['direction'] ? 'ASC' : 'DESC');
            }
        }
    }

    /**
     * @param string $sortBy
     *
     * @todo : we should make UI and API formats uniform to avoid such transformations
     *
     * @return array
     */
    private function parseSortBy($sortBy)
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
    private function parseFilters(array $filters)
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
    private function decodeFilters(array $filters)
    {
        $decodedFilters = [];
        foreach ($filters as $property => $value) {
            $decodedFilters[] = ['value' => $value, 'property' => $property];
        }

        return $decodedFilters;
    }
}
