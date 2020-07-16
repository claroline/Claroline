<?php

namespace Claroline\CursusBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/07/21 06:18:07
 */
class Version20200721061805 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course 
            ADD thumbnail VARCHAR(255) DEFAULT NULL, 
            ADD slug VARCHAR(128) NOT NULL,
            ADD parent_id INT DEFAULT NULL,
            CHANGE icon poster VARCHAR(255) DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_cursus 
            DROP icon
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_3359D349989D9B62 ON claro_cursusbundle_course (slug)
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course 
            ADD CONSTRAINT FK_3359D349727ACA70 FOREIGN KEY (parent_id) 
            REFERENCES claro_cursusbundle_course (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE INDEX IDX_3359D349727ACA70 ON claro_cursusbundle_course (parent_id)
        ');

        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event 
            DROP FOREIGN KEY FK_257C3061F7DBE00F
        ');
        $this->addSql('
            DROP INDEX IDX_257C3061F7DBE00F ON claro_cursusbundle_session_event
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event 
            DROP event_set
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course 
            DROP FOREIGN KEY FK_3359D349727ACA70
        ');
        $this->addSql('
            DROP INDEX IDX_3359D349727ACA70 ON claro_cursusbundle_course
        ');
        $this->addSql('
            DROP INDEX UNIQ_3359D349989D9B62 ON claro_cursusbundle_course
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course 
            ADD icon VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, 
            DROP slug,
            DROP poster, 
            DROP thumbnail,
            DROP parent_id
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_cursus 
            ADD icon VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event 
            ADD event_set INT DEFAULT NULL
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
}
