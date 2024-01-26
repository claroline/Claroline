<?php

namespace Claroline\CursusBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2024/01/25 11:46:56
 */
final class Version20240125114656 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session 
            ADD invitation_template_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session 
            ADD CONSTRAINT FK_C5F56FDED2D03B8 FOREIGN KEY (invitation_template_id) 
            REFERENCES claro_template (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            CREATE INDEX IDX_C5F56FDED2D03B8 ON claro_cursusbundle_course_session (invitation_template_id)
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event 
            ADD invitation_template_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event 
            ADD CONSTRAINT FK_257C3061D2D03B8 FOREIGN KEY (invitation_template_id) 
            REFERENCES claro_template (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            CREATE INDEX IDX_257C3061D2D03B8 ON claro_cursusbundle_session_event (invitation_template_id)
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session 
            DROP FOREIGN KEY FK_C5F56FDED2D03B8
        ');
        $this->addSql('
            DROP INDEX IDX_C5F56FDED2D03B8 ON claro_cursusbundle_course_session
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session 
            DROP invitation_template_id
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event 
            DROP FOREIGN KEY FK_257C3061D2D03B8
        ');
        $this->addSql('
            DROP INDEX IDX_257C3061D2D03B8 ON claro_cursusbundle_session_event
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event 
            DROP invitation_template_id
        ');
    }
}
