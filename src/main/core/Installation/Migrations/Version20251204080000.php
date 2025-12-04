<?php

namespace Claroline\CoreBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251204080000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            UPDATE claro_workspace SET hidden = false WHERE entity_name = "default_workspace"
        ');
    }

    public function down(Schema $schema): void
    {
    }
}
