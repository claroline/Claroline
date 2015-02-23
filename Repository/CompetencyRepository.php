<?php

namespace HeVinci\CompetencyBundle\Repository;

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

class CompetencyRepository extends NestedTreeRepository
{
    public function findRootByName($name)
    {
        return $this->findBy(['name' => $name, 'parent' => null]);
    }
}
