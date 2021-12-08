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

use Claroline\CoreBundle\Entity\Update\Version;
use Doctrine\ORM\EntityRepository;

class VersionRepository extends EntityRepository
{
    public function getLatest(string $fqcn): ?Version
    {
        $fqcn = addcslashes($fqcn, '\\');

        return $this->createQueryBuilder('e')
            ->orderBy('e.date', 'DESC')
            ->where('e.bundle LIKE :bundle')
            ->setParameter('bundle', "%{$fqcn}%")
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getLatestExecuted(string $fqcn): ?Version
    {
        $fqcn = addcslashes($fqcn, '\\');

        return $this->createQueryBuilder('e')
            ->orderBy('e.date', 'DESC')
            ->where('e.isUpgraded = TRUE')
            ->andWhere('e.bundle LIKE :bundle')
            ->setMaxResults(1)
            ->setParameter('bundle', "%{$fqcn}%")
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getInstallationDate(string $version): ?\DateTimeInterface
    {
        /** @var Version $installedVersion */
        $installedVersion = $this->createQueryBuilder('e')
            ->orderBy('e.date', 'ASC') // we want the first time version appeared
            ->andWhere('e.version LIKE :version')
            ->setMaxResults(1)
            ->setParameter('version', "{$version}%")
            ->getQuery()
            ->getOneOrNullResult();

        if ($installedVersion) {
            $installationDate = new \DateTime();
            $installationDate->setTimestamp($installedVersion->getDate());

            return $installationDate;
        }

        return null;
    }
}
