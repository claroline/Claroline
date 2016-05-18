<?php

namespace Icap\BadgeBundle\Installation\Updater;

use Claroline\InstallationBundle\Updater\Updater;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;

class Updater040100 extends Updater
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(EntityManager $entityManager, Connection $connection)
    {
        $this->entityManager = $entityManager;
        $this->connection = $connection;
    }

    public function postUpdate()
    {
        $this->restoreBadgeCollections();
    }

    private function restoreBadgeCollections()
    {
        if ($this->connection->getSchemaManager()->tablesExist(array('claro_badge_collection_badges'))) {
            $this->log('Restoring badge collections...');
            $rowBadgeCollections = $this->connection->query('SELECT * FROM claro_badge_collection_badges');

            foreach ($rowBadgeCollections as $rowBadgeCollection) {
                /** @var \Icap\BadgeBundle\Entity\BadgeCollection $badgeCollection */
                $badgeCollection = $this->entityManager->getRepository('IcapBadgeBundle:BadgeCollection')->find($rowBadgeCollection['badgecollection_id']);

                if (null !== $badgeCollection) {
                    /** @var \Icap\BadgeBundle\Repository\UserBadgeRepository $userBadgeRepository */
                    $userBadgeRepository = $this->entityManager->getRepository('IcapBadgeBundle:UserBadge');
                    /** @var \Icap\BadgeBundle\Entity\UserBadge $userBadge */
                    $userBadge = $userBadgeRepository->findOneBy([
                        'user' => $badgeCollection->getUser(),
                        'badge' => $this->entityManager->getReference('IcapBadgeBundle:Badge', $rowBadgeCollection['badge_id']),
                    ]);

                    if (null !== $userBadge) {
                        $this->connection->insert('claro_badge_collection_user_badges', [
                            'badgecollection_id' => $rowBadgeCollection['badgecollection_id'],
                            'userbadge_id' => $userBadge->getId(),
                        ]);
                    }
                }
            }

            $this->connection->getSchemaManager()->dropTable('claro_badge_collection_badges');
        }
    }
}
