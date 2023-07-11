<?php

namespace Claroline\CoreBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/07/11 12:50:10
 */
final class Version20230711125009 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            DROP INDEX UNIQ_E7C393D75E237E06 ON claro_group
        ');
        $this->addSql('
            ALTER TABLE claro_group
            ADD code VARCHAR(255) NOT NULL
        ');

        $this->addSql('
            UPDATE claro_group
            SET code = name
        ');

        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_E7C393D777153098 ON claro_group (code)
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            DROP INDEX UNIQ_E7C393D777153098 ON claro_group
        ');
        $this->addSql('
            ALTER TABLE claro_group 
            DROP code
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_E7C393D75E237E06 ON claro_group (name)
        ');
    }
}
