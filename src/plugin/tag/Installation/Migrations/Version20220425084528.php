<?php

namespace Claroline\TagBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/07/10 03:02:30
 */
final class Version20220425084528 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE claro_tagbundle_tagged_object (
                id INT AUTO_INCREMENT NOT NULL, 
                tag_id INT NOT NULL, 
                object_id VARCHAR(255) NOT NULL, 
                object_class VARCHAR(255) NOT NULL, 
                object_name VARCHAR(255) DEFAULT NULL, 
                INDEX IDX_1EA1E15DBAD26311 (tag_id), 
                UNIQUE INDEX `unique` (object_id, object_class, tag_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_tagbundle_tag (
                id INT AUTO_INCREMENT NOT NULL, 
                tag_name VARCHAR(255) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                description LONGTEXT DEFAULT NULL, 
                color VARCHAR(255) DEFAULT NULL, 
                UNIQUE INDEX UNIQ_6E5EC9DB02CC1B0 (tag_name), 
                UNIQUE INDEX UNIQ_6E5EC9DD17F50A6 (uuid), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_tagbundle_tagged_object 
            ADD CONSTRAINT FK_1EA1E15DBAD26311 FOREIGN KEY (tag_id) 
            REFERENCES claro_tagbundle_tag (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_tagbundle_tagged_object 
            DROP FOREIGN KEY FK_1EA1E15DBAD26311
        ');
        $this->addSql('
            DROP TABLE claro_tagbundle_tagged_object
        ');
        $this->addSql('
            DROP TABLE claro_tagbundle_tag
        ');
    }
}
