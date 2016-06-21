<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 * 
 * Date: 4/13/15
 */

namespace Icap\NotificationBundle\Installation\Updater;

use Claroline\InstallationBundle\Updater\Updater;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Icap\NotificationBundle\Entity\NotificationPluginConfiguration;

class Updater040200 extends Updater
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    private $connection;

    /**
     * @param EntityManager             $em
     * @param \Doctrine\DBAL\Connection $connection
     */
    public function __construct(EntityManager $em, Connection $connection)
    {
        $this->em = $em;
        $this->connection = $connection;
    }

    public function postUpdate()
    {
        $this->createDefaultConfiguration();
    }

    private function createDefaultConfiguration()
    {
        if ($this->connection->getSchemaManager()->tablesExist(array('icap__notification_plugin_configuration'))) {
            $this->log('Creating default configuration for notifications...');
            $configuration = $this->em->
                getRepository('IcapNotificationBundle:NotificationPluginConfiguration')->findAll();
            if (count($configuration) == 0) {
                $configuration = new NotificationPluginConfiguration();
                $this->em->persist($configuration);
                $this->em->flush();
            }
        }
    }
}
