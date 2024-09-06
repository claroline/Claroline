<?php

namespace Claroline\AppBundle\Repository;

/**
 * Helper to search if a string value is used and will increment it till the value is not used anymore.
 * This is useful to generate unique names or codes.
 */
trait UniqueValueFinder
{
    abstract protected function getEntityManager();

    abstract protected function getEntityName();

    /**
     * Generates a unique value from given one by iterating it.
     */
    public function findNextUnique(string $propName, string $searchValue, string $incrementSeparator = '_'): string
    {
        // search in DB
        $existingValues = $this->findByPropWithPrefix($propName, $searchValue);

        $getter = 'get'.ucfirst($propName);

        // search in pending insertions
        $toInsert = $this->getEntityManager()->getUnitOfWork()->getScheduledEntityInsertions();
        foreach ($toInsert as $entityInsertion) {
            if (is_a($entityInsertion, $this->getEntityName())) {
                $entityValue = strtolower($entityInsertion->$getter());
                if (str_starts_with($entityValue, strtolower($searchValue))) {
                    $existingValues[] = $entityValue;
                }
            }
        }

        // search in pending updates
        $toUpdate = $this->getEntityManager()->getUnitOfWork()->getScheduledEntityUpdates();
        foreach ($toUpdate as $entityUpdate) {
            if (is_a($entityUpdate, $this->getEntityName())) {
                $entityValue = strtolower($entityUpdate->$getter());
                if (str_starts_with($entityValue, strtolower($searchValue))) {
                    $existingValues[] = $entityValue;
                }
            }
        }

        if (empty($existingValues)) {
            return $searchValue;
        }

        $index = count($existingValues);
        do {
            ++$index;
            $currentValue = $searchValue.$incrementSeparator.$index;
            $lowerCurrentValue = strtolower($currentValue);
        } while (in_array($lowerCurrentValue, $existingValues));

        return $currentValue;
    }

    private function findByPropWithPrefix(string $propName, string $prefix): array
    {
        $found = $this->getEntityManager()->createQueryBuilder()
            ->select("LOWER(obj.$propName) AS prop")
            ->from($this->getEntityName(), 'obj')
            ->where("LOWER(obj.$propName) LIKE :search")
            ->setParameter('search', strtolower(addcslashes($prefix, '%_')).'%')
            ->getQuery()
            ->getResult()
        ;

        return array_map(function (array $ws) {
            return $ws['prop'];
        }, $found);
    }
}
