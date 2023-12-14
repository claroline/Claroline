<?php

namespace Claroline\LogBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/12/14 07:16:36
 */
final class Version20231214071624 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_log_operational 
            ADD objectClass VARCHAR(255) NOT NULL, 
            ADD objectId VARCHAR(255) NOT NULL, 
            ADD changeset LONGTEXT NOT NULL COMMENT "(DC2Type:json)"
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_log_operational 
            DROP objectClass, 
            DROP objectId, 
            DROP changeset
        ');
    }
}
