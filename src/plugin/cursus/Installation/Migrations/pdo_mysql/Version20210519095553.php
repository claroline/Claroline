<?php

namespace Claroline\CursusBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/05/19 09:55:55
 */
class Version20210519095553 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event 
            ADD presence_template_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event 
            ADD CONSTRAINT FK_257C3061D7F5EEA3 FOREIGN KEY (presence_template_id) 
            REFERENCES claro_template (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            CREATE INDEX IDX_257C3061D7F5EEA3 ON claro_cursusbundle_session_event (presence_template_id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event 
            DROP FOREIGN KEY FK_257C3061D7F5EEA3
        ');
        $this->addSql('
            DROP INDEX IDX_257C3061D7F5EEA3 ON claro_cursusbundle_session_event
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event 
            DROP presence_template_id
        ');
    }
}
