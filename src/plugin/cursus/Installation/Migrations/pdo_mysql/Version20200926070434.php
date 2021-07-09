<?php

namespace Claroline\CursusBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/09/26 07:04:56
 */
class Version20200926070434 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course 
            ADD creator_id INT DEFAULT NULL, 
            ADD createdAt DATETIME DEFAULT NULL, 
            ADD updatedAt DATETIME DEFAULT NULL, 
            DROP organization_validation, 
            CHANGE display_order display_order INT DEFAULT 1 NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course 
            ADD CONSTRAINT FK_3359D34961220EA6 FOREIGN KEY (creator_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            CREATE INDEX IDX_3359D34961220EA6 ON claro_cursusbundle_course (creator_id)
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session 
            ADD creator_id INT DEFAULT NULL,  
            ADD updatedAt DATETIME DEFAULT NULL,  
            DROP organization_validation, 
            CHANGE creation_date createdAt DATETIME DEFAULT NULL,
            CHANGE details details LONGTEXT DEFAULT NULL COMMENT "(DC2Type:json)", 
            CHANGE display_order display_order INT DEFAULT 1 NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session 
            ADD CONSTRAINT FK_C5F56FDE61220EA6 FOREIGN KEY (creator_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            CREATE INDEX IDX_C5F56FDE61220EA6 ON claro_cursusbundle_course_session (creator_id)
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course 
            DROP FOREIGN KEY FK_3359D34961220EA6
        ');
        $this->addSql('
            DROP INDEX IDX_3359D34961220EA6 ON claro_cursusbundle_course
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course 
            ADD organization_validation TINYINT(1) NOT NULL, 
            DROP creator_id, 
            DROP createdAt, 
            DROP updatedAt, 
            CHANGE display_order display_order INT DEFAULT 500 NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session 
            DROP FOREIGN KEY FK_C5F56FDE61220EA6
        ');
        $this->addSql('
            DROP INDEX IDX_C5F56FDE61220EA6 ON claro_cursusbundle_course_session
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session  
            ADD organization_validation TINYINT(1) NOT NULL, 
            DROP creator_id, 
            DROP updatedAt, 
            ADD createdAt creation_date DATETIME NOT NULL,
            CHANGE details details LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci` COMMENT "(DC2Type:json_array)", 
            CHANGE display_order display_order INT DEFAULT 500 NOT NULL
        ');
    }
}
