<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2017/02/08 11:04:31
 */
class Version20170208110429 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_public_file_use (
                id INT AUTO_INCREMENT NOT NULL, 
                public_file_id INT DEFAULT NULL, 
                object_uuid VARCHAR(255) NOT NULL, 
                object_class VARCHAR(255) NOT NULL, 
                object_name VARCHAR(255) DEFAULT NULL, 
                INDEX IDX_6F128157C81526DE (public_file_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_public_file (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                file_size INT DEFAULT NULL, 
                filename VARCHAR(255) NOT NULL, 
                hash_name VARCHAR(255) NOT NULL, 
                directory_name VARCHAR(255) NOT NULL, 
                creation_date DATETIME NOT NULL, 
                mime_type VARCHAR(255) DEFAULT NULL, 
                INDEX IDX_7C1E45A0A76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_public_file_use 
            ADD CONSTRAINT FK_6F128157C81526DE FOREIGN KEY (public_file_id) 
            REFERENCES claro_public_file (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_public_file 
            ADD CONSTRAINT FK_7C1E45A0A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_public_file_use 
            DROP FOREIGN KEY FK_6F128157C81526DE
        ');
        $this->addSql('
            DROP TABLE claro_public_file_use
        ');
        $this->addSql('
            DROP TABLE claro_public_file
        ');
    }
}
