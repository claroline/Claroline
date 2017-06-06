<?php

namespace Claroline\CursusBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2017/06/06 02:56:53
 */
class Version20170606145651 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_cursusbundle_session_event_set (
                id INT AUTO_INCREMENT NOT NULL, 
                session_id INT DEFAULT NULL, 
                set_name VARCHAR(255) NOT NULL, 
                set_limit INT NOT NULL, 
                INDEX IDX_C400AB6D613FECDF (session_id), 
                UNIQUE INDEX event_set_unique_name_session (set_name, session_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event_set 
            ADD CONSTRAINT FK_C400AB6D613FECDF FOREIGN KEY (session_id) 
            REFERENCES claro_cursusbundle_course_session (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event 
            ADD event_set INT DEFAULT NULL, 
            ADD event_type INT DEFAULT 0 NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event 
            ADD CONSTRAINT FK_257C3061F7DBE00F FOREIGN KEY (event_set) 
            REFERENCES claro_cursusbundle_session_event_set (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            CREATE INDEX IDX_257C3061F7DBE00F ON claro_cursusbundle_session_event (event_set)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event 
            DROP FOREIGN KEY FK_257C3061F7DBE00F
        ');
        $this->addSql('
            DROP TABLE claro_cursusbundle_session_event_set
        ');
        $this->addSql('
            DROP INDEX IDX_257C3061F7DBE00F ON claro_cursusbundle_session_event
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event 
            DROP event_set, 
            DROP event_type
        ');
    }
}
