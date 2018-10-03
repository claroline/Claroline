<?php

namespace Claroline\CoreBundle\Library\Installation\Updater;

use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater120020 extends Updater
{
    protected $logger;
    private $conn;

    public function __construct(ContainerInterface $container, $logger = null)
    {
        $this->logger = $logger;
        $this->conn = $container->get('doctrine.dbal.default_connection');
    }

    public function postUpdate()
    {
        $this->log('Update list parameters...');

        $this->updatePagination('claro_widget_list');
        $this->updatePagination('claro_directory');
    }

    private function updatePagination($table)
    {
        // update pagination sizes
        $stmt = $this->conn->prepare("
            UPDATE {$table} SET availablePageSizes = REPLACE(availablePageSizes, '20', '30') WHERE availablePageSizes LIKE '%20%'
        ");
        $stmt->execute();

        $stmt = $this->conn->prepare("
            UPDATE {$table} SET availablePageSizes = REPLACE(availablePageSizes, '10', '15') WHERE availablePageSizes LIKE '%10,%' OR availablePageSizes LIKE '%10]%'
        ");
        $stmt->execute();

        $stmt = $this->conn->prepare("
            UPDATE {$table} SET availablePageSizes = REPLACE(availablePageSizes, '100', '120') WHERE availablePageSizes LIKE '%100%'
        ");
        $stmt->execute();

        // update default page size
        $stmt = $this->conn->prepare("
            UPDATE {$table} SET pageSize = 30 WHERE pageSize = 20
        ");
        $stmt->execute();

        $stmt = $this->conn->prepare("
            UPDATE {$table} SET pageSize = 15 WHERE pageSize = 10
        ");
        $stmt->execute();

        $stmt = $this->conn->prepare("
            UPDATE {$table} SET pageSize = 100 WHERE pageSize = 120
        ");
        $stmt->execute();
    }
}
