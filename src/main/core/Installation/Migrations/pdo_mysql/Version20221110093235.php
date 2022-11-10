<?php

namespace Claroline\CoreBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2022/11/10 09:32:44
 */
class Version20221110093235 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_public_file 
            DROP FOREIGN KEY FK_7C1E45A0A76ED395
        ');
        $this->addSql('
            DROP INDEX IDX_7C1E45A0A76ED395 ON claro_public_file
        ');
        $this->addSql('
            ALTER TABLE claro_public_file 
            ADD uuid VARCHAR(36) NOT NULL, 
            DROP user_id, 
            DROP directory_name, 
            DROP creation_date, 
            DROP source_type
        ');
        $this->addSql('
            UPDATE claro_public_file SET uuid = (SELECT UUID()) 
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_7C1E45A0D17F50A6 ON claro_public_file (uuid)
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            DROP INDEX UNIQ_7C1E45A0D17F50A6 ON claro_public_file
        ');
        $this->addSql('
            ALTER TABLE claro_public_file 
            ADD user_id INT DEFAULT NULL, 
            ADD directory_name VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, 
            ADD creation_date DATETIME NOT NULL, 
            ADD source_type VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, 
            DROP uuid
        ');
        $this->addSql('
            ALTER TABLE claro_public_file 
            ADD CONSTRAINT FK_7C1E45A0A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            CREATE INDEX IDX_7C1E45A0A76ED395 ON claro_public_file (user_id)
        ');
    }
}
