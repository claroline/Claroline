<?php

namespace Claroline\CursusBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2024/09/12 02:41:32
 */
final class Version20240912144131 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session 
            ADD canceled_template_id INT DEFAULT NULL, 
            ADD canceled TINYINT(1) NOT NULL, 
            ADD cancel_reason LONGTEXT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session 
            ADD CONSTRAINT FK_C5F56FDE4FDA7C5E FOREIGN KEY (canceled_template_id) 
            REFERENCES claro_template (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            CREATE INDEX IDX_C5F56FDE4FDA7C5E ON claro_cursusbundle_course_session (canceled_template_id)
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session 
            DROP FOREIGN KEY FK_C5F56FDE4FDA7C5E
        ');
        $this->addSql('
            DROP INDEX IDX_C5F56FDE4FDA7C5E ON claro_cursusbundle_course_session
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session 
            DROP canceled_template_id, 
            DROP canceled, 
            DROP cancel_reason
        ');
    }
}
