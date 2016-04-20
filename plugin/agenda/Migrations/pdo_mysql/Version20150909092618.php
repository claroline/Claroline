<?php

namespace Claroline\AgendaBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/09/09 09:26:18
 */
class Version20150909092618 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_event_invitation (
                id INT AUTO_INCREMENT NOT NULL, 
                event INT NOT NULL, 
                user_id INT NOT NULL, 
                status SMALLINT NOT NULL, 
                title VARCHAR(50) DEFAULT NULL, 
                description LONGTEXT DEFAULT NULL, 
                INDEX IDX_19D2F4603BAE0AA7 (event), 
                INDEX IDX_19D2F460A76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_event_invitation 
            ADD CONSTRAINT FK_19D2F4603BAE0AA7 FOREIGN KEY (event) 
            REFERENCES claro_event (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_event_invitation 
            ADD CONSTRAINT FK_19D2F460A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE claro_event_invitation
        ');
    }
}
