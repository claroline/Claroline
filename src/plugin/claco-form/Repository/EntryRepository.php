<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ClacoFormBundle\Repository;

use Claroline\ClacoFormBundle\Entity\ClacoForm;
use Claroline\ClacoFormBundle\Entity\Entry;
use Doctrine\ORM\EntityRepository;

class EntryRepository extends EntityRepository
{
    /**
     * @param string|null $startDate
     * @param string|null $endDate
     *
     * @return Entry[]
     */
    public function findPublishedEntriesByDates(ClacoForm $clacoForm, $startDate = null, $endDate = null)
    {
        $dql = '
            SELECT e
            FROM Claroline\ClacoFormBundle\Entity\Entry e
            WHERE e.clacoForm = :clacoForm
            AND e.status = :status
        ';

        if (!is_null($startDate)) {
            $dql .= '
                AND e.publicationDate >= :startDate
            ';
        }
        if (!is_null($endDate)) {
            $dql .= '
                AND e.publicationDate <= :endDate
            ';
        }
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('clacoForm', $clacoForm);
        $query->setParameter('status', Entry::PUBLISHED);

        if (!is_null($startDate)) {
            $query->setParameter('startDate', $startDate);
        }
        if (!is_null($endDate)) {
            $query->setParameter('endDate', $endDate);
        }

        return $query->getResult();
    }

    /**
     * @param array       $categoriesIds
     * @param string|null $startDate
     * @param string|null $endDate
     *
     * @return Entry[]
     */
    public function findPublishedEntriesByCategoriesAndDates(ClacoForm $clacoForm, $categoriesIds = [], $startDate = null, $endDate = null)
    {
        $dql = '
            SELECT e
            FROM Claroline\ClacoFormBundle\Entity\Entry e
            JOIN e.categories c
            WHERE e.clacoForm = :clacoForm
            AND e.status = :status
            AND c.id IN (:categoriesIds)
        ';

        if (!is_null($startDate)) {
            $dql .= '
                AND e.publicationDate >= :startDate
            ';
        }
        if (!is_null($endDate)) {
            $dql .= '
                AND e.publicationDate <= :endDate
            ';
        }
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('clacoForm', $clacoForm);
        $query->setParameter('status', Entry::PUBLISHED);
        $query->setParameter('categoriesIds', $categoriesIds);

        if (!is_null($startDate)) {
            $query->setParameter('startDate', $startDate);
        }
        if (!is_null($endDate)) {
            $query->setParameter('endDate', $endDate);
        }

        return $query->getResult();
    }
}
