<?php

namespace Claroline\CoreBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2024/09/15 09:54:43
 */
final class Version20240915095333 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            DROP INDEX code_idx ON claro_user
        ');
        $this->addSql('
            DROP INDEX enabled_idx ON claro_user
        ');
        $this->addSql('
            ALTER TABLE claro_user CHANGE is_enabled is_disabled TINYINT(1) NOT NULL
        ');
        $this->addSql('
            CREATE INDEX disabled_idx ON claro_user (is_disabled)
        ');
        $this->addSql('
            UPDATE claro_user SET is_disabled = !is_disabled
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            DROP INDEX disabled_idx ON claro_user
        ');
        $this->addSql('
            ALTER TABLE claro_user CHANGE is_disabled is_enabled TINYINT(1) NOT NULL
        ');
        $this->addSql('
            CREATE INDEX code_idx ON claro_user (administrative_code)
        ');
        $this->addSql('
            CREATE INDEX enabled_idx ON claro_user (is_enabled)
        ');
        $this->addSql('
            UPDATE claro_user SET is_enabled = !is_enabled
        ');
    }
}
