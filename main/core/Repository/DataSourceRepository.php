<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Entity\DataSource;
use Doctrine\ORM\EntityRepository;

class DataSourceRepository extends EntityRepository
{
    /**
     * Finds all available data sources in the platform.
     * It only grabs sources from enabled plugins.
     *
     * @param array  $enabledPlugins
     * @param string $context
     *
     * @return array
     */
    public function findAllAvailable(array $enabledPlugins, $context = null)
    {
        $query = $this->createQueryBuilder('ds')
            ->leftJoin('ds.plugin', 'p')
            ->where('CONCAT(p.vendorName, p.bundleName) IN (:plugins)')
            ->setParameter('plugins', $enabledPlugins);

        if ($context) {
            $query
                ->andWhere('ds.context LIKE :context')
                ->setParameter('context', '%'.$context.'%');
        }

        return $query
            ->getQuery()
            ->getResult();
    }

    public function findByTypes(array $types, $context = null)
    {
        // I filter it afterward because the table will never be huge
        return array_filter($this->findAllAvailable($context), function (DataSource $dataSource) use ($types) {
            return in_array($dataSource->getType(), $types);
        });
    }
}
