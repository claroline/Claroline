<?php

namespace Claroline\CoreBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/12/08 08:59:00
 */
class Version20211208085858 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_directory 
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE claro_directory SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_12EEC186D17F50A6 ON claro_directory (uuid)
        ');
        $this->addSql('
            ALTER TABLE claro_file 
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE claro_file SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_EA81C80BD17F50A6 ON claro_file (uuid)
        ');
        $this->addSql('
            ALTER TABLE claro_text 
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE claro_text SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_5D9559DCD17F50A6 ON claro_text (uuid)
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            DROP INDEX UNIQ_12EEC186D17F50A6 ON claro_directory
        ');
        $this->addSql('
            ALTER TABLE claro_directory 
            DROP uuid
        ');
        $this->addSql('
            DROP INDEX UNIQ_EA81C80BD17F50A6 ON claro_file
        ');
        $this->addSql('
            ALTER TABLE claro_file 
            DROP uuid
        ');
        $this->addSql('
            DROP INDEX UNIQ_5D9559DCD17F50A6 ON claro_text
        ');
        $this->addSql('
            ALTER TABLE claro_text 
            DROP uuid
        ');
    }
}
