<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\API;

use Claroline\CoreBundle\Persistence\ObjectManager;
use Doctrine\ORM\QueryBuilder;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.api.finder")
 */
class FinderProvider
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var SerializerProvider
     */
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
        SerializerProvider $serializer)
    {
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

    public function search($class, $page = 0, $limit = -1, array $searches = [], array $serializerOptions = [])
    {
        $filters = isset($searches['filters']) ? $searches['filters'] : [];
        $sortBy = $this->decodeSortBy(isset($searches['sortBy']) ? $searches['sortBy'] : null);

        $data = $this->fetch($class, $page, $limit, $filters, $sortBy);
        $count = $this->fetch($class, $page, $limit, $filters, $sortBy, true);

        return [
            'class' => $class,
            'total' => $count,
            'results' => array_map(function ($el) use ($serializerOptions) {
                return $this->serializer->serialize($el, $serializerOptions);
            }, $data),
            'page' => $page,
            'pageSize' => $limit,
            'filters' => $this->decodeFilters($filters),
            'sortBy' => $sortBy,
        ];
    }

    private function fetch($class, $page, $limit, array $filters, array $sortBy, $count = false)
    {
        try {
            /** @var QueryBuilder $qb */
            $qb = $this->om->createQueryBuilder();

            $qb->select($count ? 'count(obj)' : 'obj')
               ->from($class, 'obj');

            // filter query - let's the finder implementation process the filters to configure query
            $this->get($class)->configureQueryBuilder($qb, $filters);

            // order query
            if (!empty($sortBy['property']) && 0 !== $sortBy['direction']) {
                $qb->orderBy('obj.'.$sortBy['property'], 1 === $sortBy['property'] ? 'ASC' : 'DESC');
            }

            // limit query
            if (!$count && 0 < $limit) {
                $qb->setFirstResult($page * $limit);
                $qb->setMaxResults($limit);
            }

            $query = $qb->getQuery();

            return $count ? (int) $query->getSingleScalarResult() : $query->getResult();
        } catch (FinderException $e) {
            $data = $this->om->getRepository($class)->findBy($filters, null, $limit, $page);

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
    private function decodeSortBy($sortBy)
    {
        // default values
        $property = null;
        $direction = 0;

        if (!empty($sortBy)) {
            if ('-' === substr($sortBy, 0, 1)) {
                $property = substr($sortBy, 0, 1);
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
     * @param array $filters
     *
     * @todo : we should make UI and API formats uniform to avoid such transformations
     *
     * @return array
     */
    private function decodeFilters(array $filters)
    {
        $decodedFilters = [];

        if (!empty($filters)) {
            foreach ($filters as $property => $value) {
                $decodedFilters[] = ['value' => $value, 'property' => $property];
            }
        }

        return $decodedFilters;
    }
}
