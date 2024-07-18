<?php

namespace Claroline\CoreBundle\Installation\Updater;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\InstallationBundle\Updater\Helper\RemovePluginTrait;
use Claroline\InstallationBundle\Updater\Updater;
use Doctrine\DBAL\Connection;

class Updater150000 extends Updater
{
    use RemovePluginTrait;

    public function __construct(
        private readonly Connection $connection,
        private readonly ObjectManager $om
    ) {
    }

    public function postUpdate(): void
    {
        $this->removePlugin('Icap', 'NotificationBundle');
        $this->removePlugin('Icap', 'BibliographyBundle');
        $this->removePlugin('Claroline', 'RssBundle');
        $this->removePlugin('Icap', 'FormulaPluginBundle');
        $this->removePlugin('Claroline', 'HistoryBundle');

        $deleteTool = $this->connection->prepare(
            'DELETE FROM claro_ordered_tool WHERE tool_name = "notifications"'
        );
        $deleteTool->executeQuery();
    }
}
