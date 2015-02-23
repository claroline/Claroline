<?php

namespace HeVinci\CompetencyBundle\Repository;

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

class CompetencyRepository extends NestedTreeRepository
{
    /**
     * Returns the competency roots by name (used by validator).
     *
     * @param string $name
     * @return array
     */
    public function findRootsByName($name)
    {
        return $this->findBy(['name' => $name, 'parent' => null]);
    }
}
