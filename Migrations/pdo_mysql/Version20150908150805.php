<?php

namespace FormaLibre\PresenceBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/09/08 03:08:11
 */
class Version20150908150805 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE formalibre_presencebundle_rights (
                id INT AUTO_INCREMENT NOT NULL, 
                role_id INT DEFAULT NULL, 
                mask INT NOT NULL, 
                INDEX IDX_8A92280DD60322AC (role_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE formalibre_presencebundle_rights 
            ADD CONSTRAINT FK_8A92280DD60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE formalibre_presencebundle_rights
        ");
    }
}