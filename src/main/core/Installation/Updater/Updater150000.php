<?php

namespace Claroline\CoreBundle\Installation\Updater;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\OrganizationManager;
use Claroline\InstallationBundle\Updater\Helper\RemovePluginTrait;
use Claroline\InstallationBundle\Updater\Helper\RemoveToolTrait;
use Claroline\InstallationBundle\Updater\Updater;
use Doctrine\DBAL\Connection;

class Updater150000 extends Updater
{
    use RemovePluginTrait;
    use RemoveToolTrait;

    public function __construct(
        private readonly Connection $connection,
        private readonly ObjectManager $om,
        private readonly OrganizationManager $organizationManager,
        private readonly PlatformConfigurationHandler $config
    ) {
    }

    public function postUpdate(): void
    {
        $this->updateConfig();
        $this->removeOldPlugins();
        $this->removeAccountContext();

        $this->removeTool('connection_messages');
        $this->removeTool('templates');

        $this->linkWorkspacesToOrganizations();
        $this->linkLocationsToOrganizations();
    }

    private function updateConfig(): void
    {
        $locale = $this->config->getParameter('locales.default');
        if ($locale) {
            $this->config->setParameter('intl.locale', $locale);
        }
    }

    private function removeOldPlugins(): void
    {
        $this->removePlugin('Icap', 'NotificationBundle');
        $this->removePlugin('Icap', 'BibliographyBundle');
        $this->removePlugin('Claroline', 'RssBundle');
        $this->removePlugin('Icap', 'FormulaPluginBundle');
        $this->removePlugin('Claroline', 'HistoryBundle');
        $this->removePlugin('HeVinci', 'CompetencyBundle');
        $this->removePlugin('HeVinci', 'FavouriteBundle');
        $this->removePlugin('Claroline', 'SlideshowBundle');
        $this->removePlugin('Claroline', 'TextPlayerBundle');

        $this->removeTool('notifications');
    }

    private function removeAccountContext(): void
    {
        $deleteTool = $this->connection->prepare(
            'DELETE FROM claro_ordered_tool WHERE context_name = "account"'
        );
        $deleteTool->executeQuery();
    }

    private function linkWorkspacesToOrganizations(): void
    {
        $organization = $this->organizationManager->getDefault(true);

        $updateQuery = $this->connection->prepare('
            INSERT INTO workspace_organization (workspace_id, organization_id)
            SELECT w.id AS workspace_id, :default_organization_id AS organization_id
            FROM claro_workspace AS w 
            LEFT JOIN workspace_organization AS wo ON w.id = wo.workspace_id 
            WHERE wo.organization_id IS NULL
        ');

        $updateQuery->bindValue('default_organization_id', $organization->getId());
        $updateQuery->executeQuery();
    }

    private function linkLocationsToOrganizations(): void
    {
        $organization = $this->organizationManager->getDefault(true);

        $updateQuery = $this->connection->prepare('
            INSERT INTO claro__location_organization (location_id, organization_id)
            SELECT l.id AS location_id, :default_organization_id AS organization_id
            FROM claro__location AS l 
            LEFT JOIN claro__location_organization AS lo ON l.id = lo.location_id 
            WHERE lo.organization_id IS NULL
        ');

        $updateQuery->bindValue('default_organization_id', $organization->getId());
        $updateQuery->executeQuery();
    }
}
