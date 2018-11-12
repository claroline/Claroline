<?php

namespace Claroline\CoreBundle\Library\Installation\Updater;

use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Yaml;

class Updater120118 extends Updater
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
        $this->updateListConfig();
    }

    private function updateListConfig()
    {
        $this->log('Remove sorting of filters from list config (format has changed).');

        $this->conn
            ->prepare('UPDATE claro_directory SET sortBy = null, availableSort = "[]", filters = "[]", availableFilters = "[]"')
            ->execute();

        $this->conn
            ->prepare('UPDATE claro_widget_list SET sortBy = null, availableSort = "[]", filters = "[]", availableFilters = "[]"')
            ->execute();
    }
}
