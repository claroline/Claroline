<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2017/11/19 02:03:25
 */
class Version20171119140324 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_resource_thumbnail (
                id INT AUTO_INCREMENT NOT NULL, 
                shortcut_id INT DEFAULT NULL, 
                mimeType VARCHAR(255) NOT NULL, 
                is_shortcut TINYINT(1) NOT NULL, 
                relative_url VARCHAR(255) DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_FF244542D17F50A6 (uuid), 
                INDEX IDX_FF24454279F0D498 (shortcut_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_resource_thumbnail 
            ADD CONSTRAINT FK_FF24454279F0D498 FOREIGN KEY (shortcut_id) 
            REFERENCES claro_resource_icon (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_resource_node 
            ADD thumbnail_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_resource_node 
            ADD CONSTRAINT FK_A76799FFFDFF2E92 FOREIGN KEY (thumbnail_id) 
            REFERENCES claro_resource_thumbnail (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_A76799FFFDFF2E92 ON claro_resource_node (thumbnail_id)
        ');
        $this->addSql('
            ALTER TABLE claro_scheduled_task 
            DROP executed
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_resource_node 
            DROP FOREIGN KEY FK_A76799FFFDFF2E92
        ');
        $this->addSql('
            DROP TABLE claro_resource_thumbnail
        ');
        $this->addSql('
            DROP INDEX UNIQ_A76799FFFDFF2E92 ON claro_resource_node
        ');
        $this->addSql('
            ALTER TABLE claro_resource_node 
            DROP thumbnail_id
        ');
        $this->addSql('
            ALTER TABLE claro_scheduled_task 
            ADD executed TINYINT(1) NOT NULL
        ');
    }
}
