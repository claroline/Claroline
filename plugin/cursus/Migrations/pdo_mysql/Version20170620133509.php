<?php

namespace Claroline\CursusBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2017/06/20 01:35:10
 */
class Version20170620133509 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course
            DROP FOREIGN KEY FK_3359D349EE7F5384
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE claro_cursusbundle_course
            SET uuid = (SELECT UUID())
         ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course
            ADD CONSTRAINT FK_3359D349EE7F5384 FOREIGN KEY (workspace_model_id)
            REFERENCES claro_workspace (id)
            ON DELETE SET NULL
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_3359D349D17F50A6 ON claro_cursusbundle_course (uuid)
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session_registration_queue
            DROP FOREIGN KEY FK_334FC296613FECDF
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session_registration_queue CHANGE session_id session_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session_registration_queue
            ADD CONSTRAINT FK_334FC296613FECDF FOREIGN KEY (session_id)
            REFERENCES claro_cursusbundle_course_session (id)
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_cursus
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE claro_cursusbundle_cursus
            SET uuid = (SELECT UUID())
         ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_27921C33D17F50A6 ON claro_cursusbundle_cursus (uuid)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course
            DROP FOREIGN KEY FK_3359D349EE7F5384
        ');
        $this->addSql('
            DROP INDEX UNIQ_3359D349D17F50A6 ON claro_cursusbundle_course
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course
            DROP uuid
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course
            ADD CONSTRAINT FK_3359D349EE7F5384 FOREIGN KEY (workspace_model_id)
            REFERENCES claro_workspace_model (id)
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session_registration_queue
            DROP FOREIGN KEY FK_334FC296613FECDF
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session_registration_queue CHANGE session_id session_id INT NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session_registration_queue
            ADD CONSTRAINT FK_334FC296613FECDF FOREIGN KEY (session_id)
            REFERENCES claro_cursusbundle_course_session (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            DROP INDEX UNIQ_27921C33D17F50A6 ON claro_cursusbundle_cursus
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_cursus
            DROP uuid
        ');
    }
}
