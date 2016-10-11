<?php

namespace Claroline\CursusBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2016/09/05 10:18:06
 */
class Version20160905101805 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_cursusbundle_presence_status (
                id INT AUTO_INCREMENT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                presence_type INT NOT NULL, 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_cursusbundle_session_event_user (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT NOT NULL, 
                session_event_id INT NOT NULL, 
                presence_status_id INT DEFAULT NULL, 
                registration_status INT NOT NULL, 
                registration_date DATETIME DEFAULT NULL, 
                application_date DATETIME DEFAULT NULL, 
                INDEX IDX_31D741DDA76ED395 (user_id), 
                INDEX IDX_31D741DDFA5B88E3 (session_event_id), 
                INDEX IDX_31D741DDD079F0B (presence_status_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event_user 
            ADD CONSTRAINT FK_31D741DDA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event_user 
            ADD CONSTRAINT FK_31D741DDFA5B88E3 FOREIGN KEY (session_event_id) 
            REFERENCES claro_cursusbundle_session_event (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event_user 
            ADD CONSTRAINT FK_31D741DDD079F0B FOREIGN KEY (presence_status_id) 
            REFERENCES claro_cursusbundle_presence_status (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session 
            ADD event_registration_type INT DEFAULT 0 NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event 
            ADD max_users INT DEFAULT NULL, 
            ADD registration_type INT DEFAULT 0 NOT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event_user 
            DROP FOREIGN KEY FK_31D741DDD079F0B
        ');
        $this->addSql('
            DROP TABLE claro_cursusbundle_presence_status
        ');
        $this->addSql('
            DROP TABLE claro_cursusbundle_session_event_user
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session 
            DROP event_registration_type
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event 
            DROP max_users, 
            DROP registration_type
        ');
    }
}
