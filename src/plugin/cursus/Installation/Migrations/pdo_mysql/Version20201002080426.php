<?php

namespace Claroline\CursusBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/10/02 08:04:27
 */
class Version20201002080426 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course 
            CHANGE display_order entity_order INT NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session 
            CHANGE display_order entity_order INT NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_presence_status 
            ADD uuid VARCHAR(36) NOT NULL, 
            CHANGE presence_type event_user_id INT NOT NULL, 
            CHANGE name presence_status VARCHAR(255) NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_presence_status 
            ADD CONSTRAINT FK_DFE5E1FE22397A3A FOREIGN KEY (event_user_id) 
            REFERENCES claro_cursusbundle_session_event_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_DFE5E1FED17F50A6 ON claro_cursusbundle_presence_status (uuid)
        ');
        $this->addSql('
            CREATE INDEX IDX_DFE5E1FE22397A3A ON claro_cursusbundle_presence_status (event_user_id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course 
            CHANGE entity_order display_order INT DEFAULT 1 NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session 
            CHANGE entity_order display_order INT DEFAULT 1 NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_presence_status 
            DROP FOREIGN KEY FK_DFE5E1FE22397A3A
        ');
        $this->addSql('
            DROP INDEX UNIQ_DFE5E1FED17F50A6 ON claro_cursusbundle_presence_status
        ');
        $this->addSql('
            DROP INDEX IDX_DFE5E1FE22397A3A ON claro_cursusbundle_presence_status
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_presence_status 
            DROP uuid, 
            CHANGE presence_status name VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, 
            CHANGE event_user_id presence_type INT NOT NULL
        ');
    }
}
