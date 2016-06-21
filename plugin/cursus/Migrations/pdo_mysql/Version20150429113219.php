<?php

namespace Claroline\CursusBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/04/29 11:32:21
 */
class Version20150429113219 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_cursusbundle_course_registration_queue (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT NOT NULL, 
                course_id INT NOT NULL, 
                application_date DATETIME NOT NULL, 
                INDEX IDX_E068776EA76ED395 (user_id), 
                INDEX IDX_E068776E591CC992 (course_id), 
                UNIQUE INDEX course_queue_unique_course_user (course_id, user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_registration_queue 
            ADD CONSTRAINT FK_E068776EA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_registration_queue 
            ADD CONSTRAINT FK_E068776E591CC992 FOREIGN KEY (course_id) 
            REFERENCES claro_cursusbundle_course (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course 
            ADD icon VARCHAR(255) DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_cursus 
            ADD icon VARCHAR(255) DEFAULT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE claro_cursusbundle_course_registration_queue
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course 
            DROP icon
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_cursus 
            DROP icon
        ');
    }
}
