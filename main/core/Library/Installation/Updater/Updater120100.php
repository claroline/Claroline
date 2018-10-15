<?php

namespace Claroline\CoreBundle\Library\Installation\Updater;

use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater120100 extends Updater
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
        $this->setDirectoryDefaults();
    }

    public function setDirectoryDefaults()
    {
        $this->log('Set directories default list config');

        $this->conn
            ->prepare('
                UPDATE claro_directory
                SET 
                    show_summary = 1,
                    open_summary = 0,
                    filterable = 1,
                    sortable = 1,
                    paginated = 1,
                    sortBy = "name",
                    pageSize = 30,
                    display = "tiles-sm",
                    availableDisplays = "[\"table\",\"table-sm\",\"tiles\",\"tiles-sm\",\"list\",\"list-sm\"]",
                    availableColumns = "[\"name\",\"meta.type\",\"parent\",\"meta.published\", \"meta.updated\",\"meta.created\"]",
                    displayedColumns = "[\"name\",\"meta.type\",\"parent\",\"meta.published\"]",
                    availablePageSizes = "[15,30,50,100,-1]",
                    availableFilters = "[\"name\",\"meta.type\",\"parent\",\"meta.published\"]",
                    columnsFilterable = 1,
                    count = 1,
                    availableSort = "[\"name\",\"meta.type\",\"meta.updated\", \"meta.created\"]"
            ')
            ->execute();
    }
}
