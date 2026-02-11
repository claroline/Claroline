<?php

namespace Claroline\CursusBundle\Installation\Updater;

use Claroline\CoreBundle\Manager\OrganizationManager;
use Claroline\InstallationBundle\Updater\Updater;
use Doctrine\DBAL\Connection;

class Updater150000 extends Updater
{
    public function __construct(
        private readonly Connection $connection,
        private readonly OrganizationManager $organizationManager
    ) {
    }

    public function postUpdate(): void
    {
        $this->linkCoursesToOrganizations();

        // migrate registered groups to sessions and events
    }

    private function linkCoursesToOrganizations(): void
    {
        $organization = $this->organizationManager->getDefault(true);

        $updateQuery = $this->connection->prepare('
            INSERT INTO claro_cursusbundle_course_organizations (course_id, organization_id)
            SELECT c.id AS course_id, :default_organization_id AS organization_id
            FROM claro_cursusbundle_course AS c 
            LEFT JOIN claro_cursusbundle_course_organizations AS co ON c.id = co.course_id 
            WHERE co.organization_id IS NULL
        ');

        $updateQuery->bindValue('default_organization_id', $organization->getId());
        $updateQuery->executeQuery();
    }
}
