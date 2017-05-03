<?php

namespace Icap\BibliographyBundle\Repository;

use Doctrine\ORM\EntityRepository;

class BookReferenceConfigurationRepository extends EntityRepository
{
    /**
     * @return bool
     */
    public function isApiConfigured()
    {
        return $this->createQueryBuilder('bookReferenceConfiguration')
            ->where('bookReferenceConfiguration.api_key = \'\'')
            ->orWhere('bookReferenceConfiguration.api_key IS NULL')
            ->getQuery()
            ->getOneOrNullResult()
            ? false : true;
    }
}
