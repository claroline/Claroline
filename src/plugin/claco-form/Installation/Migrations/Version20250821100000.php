<?php

namespace Claroline\ClacoFormBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250821100000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            UPDATE claro_resource_node AS r
            LEFT JOIN claro_resource_type AS t ON (r.resource_type_id = t.id)
            SET r.downloadable = 1
            WHERE t.name = "claroline_claco_form"
        ');
    }

    public function down(Schema $schema): void
    {
    }
}
