<?php

namespace Claroline\CursusBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/04/29 11:32:22
 */
class Version20150429113219 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_cursusbundle_course_registration_queue (
                id INT IDENTITY NOT NULL, 
                user_id INT NOT NULL, 
                course_id INT NOT NULL, 
                application_date DATETIME2(6) NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_E068776EA76ED395 ON claro_cursusbundle_course_registration_queue (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_E068776E591CC992 ON claro_cursusbundle_course_registration_queue (course_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX course_queue_unique_course_user ON claro_cursusbundle_course_registration_queue (course_id, user_id) 
            WHERE course_id IS NOT NULL 
            AND user_id IS NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_cursusbundle_course_registration_queue 
            ADD CONSTRAINT FK_E068776EA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_cursusbundle_course_registration_queue 
            ADD CONSTRAINT FK_E068776E591CC992 FOREIGN KEY (course_id) 
            REFERENCES claro_cursusbundle_course (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_cursusbundle_course 
            ADD icon NVARCHAR(255)
        ");
        $this->addSql("
            ALTER TABLE claro_cursusbundle_cursus 
            ADD icon NVARCHAR(255)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_cursusbundle_course_registration_queue
        ");
        $this->addSql("
            ALTER TABLE claro_cursusbundle_course 
            DROP COLUMN icon
        ");
        $this->addSql("
            ALTER TABLE claro_cursusbundle_cursus 
            DROP COLUMN icon
        ");
    }
}