<?php

namespace Icap\WebsiteBundle\Installation\Updater;

use Claroline\InstallationBundle\Updater\Updater;
use Doctrine\DBAL\Driver\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater090300 extends Updater
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(ContainerInterface $container)
    {
        $this->connection = $container->get('doctrine.dbal.default_connection');
    }

    public function postUpdate()
    {
        $this->syncWebsiteTitles();
    }

    /**
     * Make sure each website uses the most up-to-date resource node titles to which it belongs.
     */
    private function syncWebsiteTitles()
    {
        $this->log('Save most up-to-date website titles in database...');
        $this->connection
            ->prepare('
                UPDATE `icap__website_page` AS wp
                LEFT JOIN `icap__website` AS w ON (wp.website_id = w.id)
                LEFT JOIN `claro_resource_node` AS crn ON (w.resourceNode_id = crn.id)
                SET wp.title = crn.name
                WHERE wp.type = :type
            ')
            ->execute([
                'type' => 'root',
            ]);
    }
}
