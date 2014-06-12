<?php

namespace Claroline\CoreBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/06/12 11:13:33
 */
class Version20140612111331 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_activity_secondary_resources 
            ADD activityparameters_id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_activity_secondary_resources 
            ADD resourcenode_id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_activity_secondary_resources 
            DROP COLUMN activity_parameters_id
        ");
        $this->addSql("
            ALTER TABLE claro_activity_secondary_resources 
            DROP COLUMN resource_node_id
        ");
        $this->addSql("
            ALTER TABLE claro_activity_secondary_resources 
            DROP CONSTRAINT FK_713242A71BAD783F
        ");
        $this->addSql("
            ALTER TABLE claro_activity_secondary_resources 
            DROP CONSTRAINT FK_713242A7896F55DB
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_713242A7896F55DB'
            ) 
            ALTER TABLE claro_activity_secondary_resources 
            DROP CONSTRAINT IDX_713242A7896F55DB ELSE 
            DROP INDEX IDX_713242A7896F55DB ON claro_activity_secondary_resources
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_713242A71BAD783F'
            ) 
            ALTER TABLE claro_activity_secondary_resources 
            DROP CONSTRAINT IDX_713242A71BAD783F ELSE 
            DROP INDEX IDX_713242A71BAD783F ON claro_activity_secondary_resources
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = '[primary]'
            ) 
            ALTER TABLE claro_activity_secondary_resources 
            DROP CONSTRAINT [primary] ELSE 
            DROP INDEX [primary] ON claro_activity_secondary_resources
        ");
        $this->addSql("
            ALTER TABLE claro_activity_secondary_resources 
            ADD CONSTRAINT FK_713242A7DB5E3CF7 FOREIGN KEY (activityparameters_id) 
            REFERENCES claro_activity_parameters (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_activity_secondary_resources 
            ADD CONSTRAINT FK_713242A777C292AE FOREIGN KEY (resourcenode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_713242A7DB5E3CF7 ON claro_activity_secondary_resources (activityparameters_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_713242A777C292AE ON claro_activity_secondary_resources (resourcenode_id)
        ");
        $this->addSql("
            ALTER TABLE claro_activity_secondary_resources 
            ADD PRIMARY KEY (
                activityparameters_id, resourcenode_id
            )
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            ADD primaryResource_id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_activity ALTER COLUMN resourceNode_id INT
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            DROP CONSTRAINT FK_E4A67CACB87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            ADD CONSTRAINT FK_E4A67CAC52410EEC FOREIGN KEY (primaryResource_id) 
            REFERENCES claro_resource_node (id)
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            ADD CONSTRAINT FK_E4A67CACB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_E4A67CAC52410EEC ON claro_activity (primaryResource_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_activity 
            DROP COLUMN primaryResource_id
        ");
        $this->addSql("
            ALTER TABLE claro_activity ALTER COLUMN resourceNode_id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            DROP CONSTRAINT FK_E4A67CAC52410EEC
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            DROP CONSTRAINT FK_E4A67CACB87FAB32
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_E4A67CAC52410EEC'
            ) 
            ALTER TABLE claro_activity 
            DROP CONSTRAINT IDX_E4A67CAC52410EEC ELSE 
            DROP INDEX IDX_E4A67CAC52410EEC ON claro_activity
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            ADD CONSTRAINT FK_E4A67CACB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id)
        ");
        $this->addSql("
            ALTER TABLE claro_activity_secondary_resources 
            ADD activity_parameters_id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_activity_secondary_resources 
            ADD resource_node_id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_activity_secondary_resources 
            DROP COLUMN activityparameters_id
        ");
        $this->addSql("
            ALTER TABLE claro_activity_secondary_resources 
            DROP COLUMN resourcenode_id
        ");
        $this->addSql("
            ALTER TABLE claro_activity_secondary_resources 
            DROP CONSTRAINT FK_713242A7DB5E3CF7
        ");
        $this->addSql("
            ALTER TABLE claro_activity_secondary_resources 
            DROP CONSTRAINT FK_713242A777C292AE
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_713242A7DB5E3CF7'
            ) 
            ALTER TABLE claro_activity_secondary_resources 
            DROP CONSTRAINT IDX_713242A7DB5E3CF7 ELSE 
            DROP INDEX IDX_713242A7DB5E3CF7 ON claro_activity_secondary_resources
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_713242A777C292AE'
            ) 
            ALTER TABLE claro_activity_secondary_resources 
            DROP CONSTRAINT IDX_713242A777C292AE ELSE 
            DROP INDEX IDX_713242A777C292AE ON claro_activity_secondary_resources
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = '[PRIMARY]'
            ) 
            ALTER TABLE claro_activity_secondary_resources 
            DROP CONSTRAINT [PRIMARY] ELSE 
            DROP INDEX [PRIMARY] ON claro_activity_secondary_resources
        ");
        $this->addSql("
            ALTER TABLE claro_activity_secondary_resources 
            ADD CONSTRAINT FK_713242A71BAD783F FOREIGN KEY (resource_node_id) 
            REFERENCES claro_resource_node (id)
        ");
        $this->addSql("
            ALTER TABLE claro_activity_secondary_resources 
            ADD CONSTRAINT FK_713242A7896F55DB FOREIGN KEY (activity_parameters_id) 
            REFERENCES claro_activity_parameters (id)
        ");
        $this->addSql("
            CREATE INDEX IDX_713242A7896F55DB ON claro_activity_secondary_resources (activity_parameters_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_713242A71BAD783F ON claro_activity_secondary_resources (resource_node_id)
        ");
        $this->addSql("
            ALTER TABLE claro_activity_secondary_resources 
            ADD PRIMARY KEY (
                activity_parameters_id, resource_node_id
            )
        ");
    }
}