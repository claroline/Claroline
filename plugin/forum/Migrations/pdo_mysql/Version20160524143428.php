<?php

namespace Claroline\ForumBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2016/05/24 02:34:30
 */
class Version20160524143428 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_forum_last_message_widget_config 
            ADD forum_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_forum_last_message_widget_config 
            ADD CONSTRAINT FK_F68C6FF429CCBAD0 FOREIGN KEY (forum_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            CREATE INDEX IDX_F68C6FF429CCBAD0 ON claro_forum_last_message_widget_config (forum_id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_forum_last_message_widget_config 
            DROP FOREIGN KEY FK_F68C6FF429CCBAD0
        ');
        $this->addSql('
            DROP INDEX IDX_F68C6FF429CCBAD0 ON claro_forum_last_message_widget_config
        ');
        $this->addSql('
            ALTER TABLE claro_forum_last_message_widget_config 
            DROP forum_id
        ');
    }
}
