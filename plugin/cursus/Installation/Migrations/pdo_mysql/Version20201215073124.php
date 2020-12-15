<?php

namespace Claroline\CursusBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/12/15 07:31:37
 */
class Version20201215073124 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_presence_status 
            DROP FOREIGN KEY FK_DFE5E1FE22397A3A
        ');
        $this->addSql('
            DROP INDEX IDX_DFE5E1FE22397A3A ON claro_cursusbundle_presence_status
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_presence_status CHANGE event_user_id user_id INT NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_presence_status 
            ADD CONSTRAINT FK_DFE5E1FEA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE INDEX IDX_DFE5E1FEA76ED395 ON claro_cursusbundle_presence_status (user_id)
        ');

        $this->addSql('
            ALTER TABLE claro_cursusbundle_presence_status 
            ADD event_id INT NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_presence_status 
            ADD CONSTRAINT FK_DFE5E1FE71F7E88B FOREIGN KEY (event_id) 
            REFERENCES claro_cursusbundle_session_event (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE INDEX IDX_DFE5E1FE71F7E88B ON claro_cursusbundle_presence_status (event_id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_presence_status 
            DROP FOREIGN KEY FK_DFE5E1FEA76ED395
        ');
        $this->addSql('
            DROP INDEX IDX_DFE5E1FEA76ED395 ON claro_cursusbundle_presence_status
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_presence_status CHANGE user_id event_user_id INT NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_presence_status 
            ADD CONSTRAINT FK_DFE5E1FE22397A3A FOREIGN KEY (event_user_id) 
            REFERENCES claro_cursusbundle_session_event_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE INDEX IDX_DFE5E1FE22397A3A ON claro_cursusbundle_presence_status (event_user_id)
        ');

        $this->addSql('
            ALTER TABLE claro_cursusbundle_presence_status 
            DROP FOREIGN KEY FK_DFE5E1FE71F7E88B
        ');
        $this->addSql('
            DROP INDEX IDX_DFE5E1FE71F7E88B ON claro_cursusbundle_presence_status
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_presence_status 
            DROP event_id
        ');
    }
}
