<?php

namespace Claroline\CoreBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250818100000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            UPDATE claro_resource_node AS r
            LEFT JOIN claro_resource_type AS t ON (r.resource_type_id = t.id)
            SET r.downloadable = 1
            WHERE t.name = "file"
        ');
    }

    public function down(Schema $schema): void
    {
    }
}
