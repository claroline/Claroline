<?php

namespace Claroline\CoreBundle\Database;

use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.database.generic_repository")
 *
 * Helper class providing transversal repository methods.
 */
class GenericRepository
{
    private $em;

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager")
     * })
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Finds a set of entities by their ids.
     *
     * @param string    $entityClass
     * @param array     $ids
     *
     * @return array[object]
     *
     * @throws MissingEntityException if any of the requested entities cannot be found
     */
    public function findByIds($entityClass, array $ids)
    {
        $idString = implode(', ', $ids);
        $dql = "SELECT entity FROM {$entityClass} entity WHERE entity.id IN ({$idString})";
        $query = $this->em->createQuery($dql);
        $entities = $query->getResult();

        if (($entityCount = count($entities)) !== ($idCount = count($ids))) {
            throw new MissingEntityException(
                "{$entityCount} out of {$idCount} ids don't match any existing entity"
            );
        }

        return $entities;
    }
}