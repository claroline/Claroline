<?php

namespace Claroline\CoreBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2022/12/03 05:20:28
 */
class Version20221203052012 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro__organization 
            ADD description LONGTEXT DEFAULT NULL, 
            ADD poster VARCHAR(255) DEFAULT NULL, 
            ADD thumbnail VARCHAR(255) DEFAULT NULL,
            CHANGE type type VARCHAR(255) DEFAULT NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro__organization 
            DROP description, 
            DROP poster, 
            DROP thumbnail,
            CHANGE type type VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`
        ');
    }
}
