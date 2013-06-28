<?php

namespace Claroline\CoreBundle\Database;

use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.database.generic_repository")
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

    public function findByIds($entityClass, array $ids)
    {
        $idString = implode(', ', $ids);
        $dql = "
            SELECT entity FROM {$entityClass} entity
            WHERE entity.id IN ({$idString})
        ";
        $query = $this->em->createQuery($dql);
        $entities = $query->getResult();

        if (($entityCount = count($entities)) !== ($idCount = count($ids))) {
            throw new MissingEntityException(
                "{$entityCount} on {$idCount} ids don't match any existing entity"
            );
        }

        return $entities;
    }
}