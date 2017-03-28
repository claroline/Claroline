<?php

namespace FormaLibre\SupportBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2017/03/22 11:00:11
 */
class Version20170322110009 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE formalibre_support_ticket_user (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                ticket_id INT DEFAULT NULL, 
                active TINYINT(1) NOT NULL, 
                activation_date DATETIME DEFAULT NULL, 
                INDEX IDX_3FA63FEEA76ED395 (user_id), 
                INDEX IDX_3FA63FEE700047D2 (ticket_id), 
                UNIQUE INDEX support_ticket_unique_user (ticket_id, user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE formalibre_support_ticket_user 
            ADD CONSTRAINT FK_3FA63FEEA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE formalibre_support_ticket_user 
            ADD CONSTRAINT FK_3FA63FEE700047D2 FOREIGN KEY (ticket_id) 
            REFERENCES formalibre_support_ticket (id) 
            ON DELETE CASCADE
        ');
        $this->addSql("
            ALTER TABLE formalibre_support_ticket 
            ADD user_active TINYINT(1) DEFAULT '1' NOT NULL, 
            ADD admin_active TINYINT(1) DEFAULT '1' NOT NULL, 
            DROP level, 
            CHANGE contact_phone contact_phone VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql('
            ALTER TABLE formalibre_support_comment 
            ADD comment_type INT DEFAULT 0 NOT NULL
        ');
        $this->addSql("
            ALTER TABLE formalibre_support_status 
            ADD description LONGTEXT DEFAULT NULL, 
            ADD locked TINYINT(1) DEFAULT '0' NOT NULL, 
            DROP status_type
        ");
        $this->addSql("
            ALTER TABLE formalibre_support_type 
            ADD description LONGTEXT DEFAULT NULL, 
            ADD locked TINYINT(1) DEFAULT '0' NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE formalibre_support_ticket_user
        ');
        $this->addSql('
            ALTER TABLE formalibre_support_comment 
            DROP comment_type
        ');
        $this->addSql('
            ALTER TABLE formalibre_support_status 
            ADD status_type INT NOT NULL, 
            DROP description, 
            DROP locked
        ');
        $this->addSql('
            ALTER TABLE formalibre_support_ticket 
            ADD level INT NOT NULL, 
            DROP user_active, 
            DROP admin_active, 
            CHANGE contact_phone contact_phone VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci
        ');
        $this->addSql('
            ALTER TABLE formalibre_support_type 
            DROP description, 
            DROP locked
        ');
    }
}
