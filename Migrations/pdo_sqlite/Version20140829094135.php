<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/08/29 09:41:37
 */
class Version20140829094135 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_workspace_registration_queue (
                id INTEGER NOT NULL, 
                role_id INTEGER NOT NULL, 
                user_id INTEGER NOT NULL, 
                workspace_id INTEGER NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_F461C538D60322AC ON claro_workspace_registration_queue (role_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F461C538A76ED395 ON claro_workspace_registration_queue (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F461C53882D40A1F ON claro_workspace_registration_queue (workspace_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX user_role_unique ON claro_workspace_registration_queue (role_id, user_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_workspace_registration_queue
        ");
    }
}