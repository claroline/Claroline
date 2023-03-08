<?php

namespace Claroline\ThemeBundle\Repository;

use Claroline\CoreBundle\Manager\PluginManager;
use Claroline\ThemeBundle\Entity\Theme;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ThemeRepository extends ServiceEntityRepository
{
    /** @var array */
    private $bundles;

    public function __construct(ManagerRegistry $registry, PluginManager $pluginManager)
    {
        $this->bundles = $pluginManager->getEnabled();

        parent::__construct($registry, Theme::class);
    }

    public function findAll(bool $onlyEnabled = false)
    {
        $dql = '
            SELECT theme 
            FROM Claroline\ThemeBundle\Entity\Theme AS theme
            LEFT JOIN theme.plugin AS p
        ';

        if ($onlyEnabled) {
            $dql .= '
                WHERE (CONCAT(p.vendorName, p.bundleName) IN (:bundles) OR theme.plugin is NULL)
            ';
        }

        $query = $this->getEntityManager()->createQuery($dql);

        if ($onlyEnabled) {
            $query->setParameter('bundles', $this->bundles);
        }

        return $query->getResult();
    }
}
