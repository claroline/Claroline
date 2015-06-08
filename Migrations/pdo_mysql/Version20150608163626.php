<?php

namespace FormaLibre\SupportBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/06/08 04:36:28
 */
class Version20150608163626 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE formalibre_ticket (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                description LONGTEXT NOT NULL, 
                creation_date DATETIME NOT NULL, 
                ticket_status INT NOT NULL, 
                priority INT NOT NULL, 
                spent_time INT NOT NULL, 
                status_date DATETIME NOT NULL, 
                details LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', 
                INDEX IDX_8A7FE139A76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE formalibre_admin_ticket (
                id INT AUTO_INCREMENT NOT NULL, 
                ticket_id INT DEFAULT NULL, 
                user_id INT DEFAULT NULL, 
                priority INT NOT NULL, 
                details LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', 
                INDEX IDX_815F85DB700047D2 (ticket_id), 
                INDEX IDX_815F85DBA76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE formalibre_ticket 
            ADD CONSTRAINT FK_8A7FE139A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE formalibre_admin_ticket 
            ADD CONSTRAINT FK_815F85DB700047D2 FOREIGN KEY (ticket_id) 
            REFERENCES formalibre_ticket (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE formalibre_admin_ticket 
            ADD CONSTRAINT FK_815F85DBA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE formalibre_admin_ticket 
            DROP FOREIGN KEY FK_815F85DB700047D2
        ");
        $this->addSql("
            DROP TABLE formalibre_ticket
        ");
        $this->addSql("
            DROP TABLE formalibre_admin_ticket
        ");
    }
}