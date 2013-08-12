<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/08/07 05:06:01
 */
class Version20130807170601 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            sp_RENAME 'claro_resource_rights.resource_id', 
            'resourceNode_id', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE claro_resource_rights ALTER COLUMN resourceNode_id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_rights 
            DROP CONSTRAINT FK_3848F48389329D25
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_3848F48389329D25'
            ) 
            ALTER TABLE claro_resource_rights 
            DROP CONSTRAINT IDX_3848F48389329D25 ELSE 
            DROP INDEX IDX_3848F48389329D25 ON claro_resource_rights
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'resource_rights_unique_resource_role'
            ) 
            ALTER TABLE claro_resource_rights 
            DROP CONSTRAINT resource_rights_unique_resource_role ELSE 
            DROP INDEX resource_rights_unique_resource_role ON claro_resource_rights
        ");
        $this->addSql("
            ALTER TABLE claro_resource_rights 
            ADD CONSTRAINT FK_3848F483B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_3848F483B87FAB32 ON claro_resource_rights (resourceNode_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX resource_rights_unique_resource_role ON claro_resource_rights (resourceNode_id, role_id) 
            WHERE resourceNode_id IS NOT NULL 
            AND role_id IS NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource 
            DROP COLUMN mime_type
        ");
        $this->addSql("
            sp_RENAME 'claro_activity.node_id', 
            'resourceNode_id', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            ADD mime_type NVARCHAR(255)
        ");
        $this->addSql("
            ALTER TABLE claro_activity ALTER COLUMN resourceNode_id INT
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            DROP CONSTRAINT FK_E4A67CAC460D9FD7
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'UNIQ_E4A67CAC460D9FD7'
            ) 
            ALTER TABLE claro_activity 
            DROP CONSTRAINT UNIQ_E4A67CAC460D9FD7 ELSE 
            DROP INDEX UNIQ_E4A67CAC460D9FD7 ON claro_activity
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            ADD CONSTRAINT FK_E4A67CACB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource (id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_E4A67CACB87FAB32 ON claro_activity (resourceNode_id) 
            WHERE resourceNode_id IS NOT NULL
        ");
        $this->addSql("
            sp_RENAME 'claro_resource_activity.resource_id', 
            'resourceNode_id', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE claro_resource_activity ALTER COLUMN resourceNode_id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_activity 
            DROP CONSTRAINT FK_DCF37C7E89329D25
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_DCF37C7E89329D25'
            ) 
            ALTER TABLE claro_resource_activity 
            DROP CONSTRAINT IDX_DCF37C7E89329D25 ELSE 
            DROP INDEX IDX_DCF37C7E89329D25 ON claro_resource_activity
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'resource_activity_unique_combination'
            ) 
            ALTER TABLE claro_resource_activity 
            DROP CONSTRAINT resource_activity_unique_combination ELSE 
            DROP INDEX resource_activity_unique_combination ON claro_resource_activity
        ");
        $this->addSql("
            ALTER TABLE claro_resource_activity 
            ADD CONSTRAINT FK_DCF37C7EB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_DCF37C7EB87FAB32 ON claro_resource_activity (resourceNode_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX resource_activity_unique_combination ON claro_resource_activity (activity_id, resourceNode_id) 
            WHERE activity_id IS NOT NULL 
            AND resourceNode_id IS NOT NULL
        ");
        $this->addSql("
            sp_RENAME 'claro_file.node_id', 
            'resourceNode_id', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE claro_file 
            ADD mime_type NVARCHAR(255)
        ");
        $this->addSql("
            ALTER TABLE claro_file ALTER COLUMN resourceNode_id INT
        ");
        $this->addSql("
            ALTER TABLE claro_file 
            DROP CONSTRAINT FK_EA81C80B460D9FD7
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'UNIQ_EA81C80B460D9FD7'
            ) 
            ALTER TABLE claro_file 
            DROP CONSTRAINT UNIQ_EA81C80B460D9FD7 ELSE 
            DROP INDEX UNIQ_EA81C80B460D9FD7 ON claro_file
        ");
        $this->addSql("
            ALTER TABLE claro_file 
            ADD CONSTRAINT FK_EA81C80BB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource (id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_EA81C80BB87FAB32 ON claro_file (resourceNode_id) 
            WHERE resourceNode_id IS NOT NULL
        ");
        $this->addSql("
            sp_RENAME 'claro_link.node_id', 
            'resourceNode_id', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE claro_link 
            ADD mime_type NVARCHAR(255)
        ");
        $this->addSql("
            ALTER TABLE claro_link ALTER COLUMN resourceNode_id INT
        ");
        $this->addSql("
            ALTER TABLE claro_link 
            DROP CONSTRAINT FK_50B267EA460D9FD7
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'UNIQ_50B267EA460D9FD7'
            ) 
            ALTER TABLE claro_link 
            DROP CONSTRAINT UNIQ_50B267EA460D9FD7 ELSE 
            DROP INDEX UNIQ_50B267EA460D9FD7 ON claro_link
        ");
        $this->addSql("
            ALTER TABLE claro_link 
            ADD CONSTRAINT FK_50B267EAB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource (id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_50B267EAB87FAB32 ON claro_link (resourceNode_id) 
            WHERE resourceNode_id IS NOT NULL
        ");
        $this->addSql("
            sp_RENAME 'claro_directory.node_id', 
            'resourceNode_id', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE claro_directory 
            ADD mime_type NVARCHAR(255)
        ");
        $this->addSql("
            ALTER TABLE claro_directory ALTER COLUMN resourceNode_id INT
        ");
        $this->addSql("
            ALTER TABLE claro_directory 
            DROP CONSTRAINT FK_12EEC186460D9FD7
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'UNIQ_12EEC186460D9FD7'
            ) 
            ALTER TABLE claro_directory 
            DROP CONSTRAINT UNIQ_12EEC186460D9FD7 ELSE 
            DROP INDEX UNIQ_12EEC186460D9FD7 ON claro_directory
        ");
        $this->addSql("
            ALTER TABLE claro_directory 
            ADD CONSTRAINT FK_12EEC186B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource (id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_12EEC186B87FAB32 ON claro_directory (resourceNode_id) 
            WHERE resourceNode_id IS NOT NULL
        ");
        $this->addSql("
            sp_RENAME 'claro_resource_shortcut.node_id', 
            'resourceNode_id', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut 
            ADD mime_type NVARCHAR(255)
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut ALTER COLUMN resourceNode_id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut 
            DROP CONSTRAINT FK_5E7F4AB8460D9FD7
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_5E7F4AB8460D9FD7'
            ) 
            ALTER TABLE claro_resource_shortcut 
            DROP CONSTRAINT IDX_5E7F4AB8460D9FD7 ELSE 
            DROP INDEX IDX_5E7F4AB8460D9FD7 ON claro_resource_shortcut
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut 
            ADD CONSTRAINT FK_5E7F4AB8B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_5E7F4AB8B87FAB32 ON claro_resource_shortcut (resourceNode_id)
        ");
        $this->addSql("
            sp_RENAME 'claro_text.node_id', 
            'resourceNode_id', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE claro_text 
            ADD mime_type NVARCHAR(255)
        ");
        $this->addSql("
            ALTER TABLE claro_text ALTER COLUMN resourceNode_id INT
        ");
        $this->addSql("
            ALTER TABLE claro_text 
            DROP CONSTRAINT FK_5D9559DC460D9FD7
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'UNIQ_5D9559DC460D9FD7'
            ) 
            ALTER TABLE claro_text 
            DROP CONSTRAINT UNIQ_5D9559DC460D9FD7 ELSE 
            DROP INDEX UNIQ_5D9559DC460D9FD7 ON claro_text
        ");
        $this->addSql("
            ALTER TABLE claro_text 
            ADD CONSTRAINT FK_5D9559DCB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource (id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_5D9559DCB87FAB32 ON claro_text (resourceNode_id) 
            WHERE resourceNode_id IS NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            sp_RENAME 'claro_activity.resourcenode_id', 
            'node_id', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            DROP COLUMN mime_type
        ");
        $this->addSql("
            ALTER TABLE claro_activity ALTER COLUMN node_id INT
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            DROP CONSTRAINT FK_E4A67CACB87FAB32
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'UNIQ_E4A67CACB87FAB32'
            ) 
            ALTER TABLE claro_activity 
            DROP CONSTRAINT UNIQ_E4A67CACB87FAB32 ELSE 
            DROP INDEX UNIQ_E4A67CACB87FAB32 ON claro_activity
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            ADD CONSTRAINT FK_E4A67CAC460D9FD7 FOREIGN KEY (node_id) 
            REFERENCES claro_resource (id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_E4A67CAC460D9FD7 ON claro_activity (node_id) 
            WHERE node_id IS NOT NULL
        ");
        $this->addSql("
            sp_RENAME 'claro_directory.resourcenode_id', 
            'node_id', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE claro_directory 
            DROP COLUMN mime_type
        ");
        $this->addSql("
            ALTER TABLE claro_directory ALTER COLUMN node_id INT
        ");
        $this->addSql("
            ALTER TABLE claro_directory 
            DROP CONSTRAINT FK_12EEC186B87FAB32
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'UNIQ_12EEC186B87FAB32'
            ) 
            ALTER TABLE claro_directory 
            DROP CONSTRAINT UNIQ_12EEC186B87FAB32 ELSE 
            DROP INDEX UNIQ_12EEC186B87FAB32 ON claro_directory
        ");
        $this->addSql("
            ALTER TABLE claro_directory 
            ADD CONSTRAINT FK_12EEC186460D9FD7 FOREIGN KEY (node_id) 
            REFERENCES claro_resource (id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_12EEC186460D9FD7 ON claro_directory (node_id) 
            WHERE node_id IS NOT NULL
        ");
        $this->addSql("
            sp_RENAME 'claro_file.resourcenode_id', 
            'node_id', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE claro_file 
            DROP COLUMN mime_type
        ");
        $this->addSql("
            ALTER TABLE claro_file ALTER COLUMN node_id INT
        ");
        $this->addSql("
            ALTER TABLE claro_file 
            DROP CONSTRAINT FK_EA81C80BB87FAB32
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'UNIQ_EA81C80BB87FAB32'
            ) 
            ALTER TABLE claro_file 
            DROP CONSTRAINT UNIQ_EA81C80BB87FAB32 ELSE 
            DROP INDEX UNIQ_EA81C80BB87FAB32 ON claro_file
        ");
        $this->addSql("
            ALTER TABLE claro_file 
            ADD CONSTRAINT FK_EA81C80B460D9FD7 FOREIGN KEY (node_id) 
            REFERENCES claro_resource (id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_EA81C80B460D9FD7 ON claro_file (node_id) 
            WHERE node_id IS NOT NULL
        ");
        $this->addSql("
            sp_RENAME 'claro_link.resourcenode_id', 
            'node_id', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE claro_link 
            DROP COLUMN mime_type
        ");
        $this->addSql("
            ALTER TABLE claro_link ALTER COLUMN node_id INT
        ");
        $this->addSql("
            ALTER TABLE claro_link 
            DROP CONSTRAINT FK_50B267EAB87FAB32
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'UNIQ_50B267EAB87FAB32'
            ) 
            ALTER TABLE claro_link 
            DROP CONSTRAINT UNIQ_50B267EAB87FAB32 ELSE 
            DROP INDEX UNIQ_50B267EAB87FAB32 ON claro_link
        ");
        $this->addSql("
            ALTER TABLE claro_link 
            ADD CONSTRAINT FK_50B267EA460D9FD7 FOREIGN KEY (node_id) 
            REFERENCES claro_resource (id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_50B267EA460D9FD7 ON claro_link (node_id) 
            WHERE node_id IS NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource 
            ADD mime_type NVARCHAR(255)
        ");
        $this->addSql("
            sp_RENAME 'claro_resource_activity.resourcenode_id', 
            'resource_id', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE claro_resource_activity ALTER COLUMN resource_id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_activity 
            DROP CONSTRAINT FK_DCF37C7EB87FAB32
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_DCF37C7EB87FAB32'
            ) 
            ALTER TABLE claro_resource_activity 
            DROP CONSTRAINT IDX_DCF37C7EB87FAB32 ELSE 
            DROP INDEX IDX_DCF37C7EB87FAB32 ON claro_resource_activity
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'resource_activity_unique_combination'
            ) 
            ALTER TABLE claro_resource_activity 
            DROP CONSTRAINT resource_activity_unique_combination ELSE 
            DROP INDEX resource_activity_unique_combination ON claro_resource_activity
        ");
        $this->addSql("
            ALTER TABLE claro_resource_activity 
            ADD CONSTRAINT FK_DCF37C7E89329D25 FOREIGN KEY (resource_id) 
            REFERENCES claro_resource (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_DCF37C7E89329D25 ON claro_resource_activity (resource_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX resource_activity_unique_combination ON claro_resource_activity (activity_id, resource_id) 
            WHERE activity_id IS NOT NULL 
            AND resource_id IS NOT NULL
        ");
        $this->addSql("
            sp_RENAME 'claro_resource_rights.resourcenode_id', 
            'resource_id', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE claro_resource_rights ALTER COLUMN resource_id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_rights 
            DROP CONSTRAINT FK_3848F483B87FAB32
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_3848F483B87FAB32'
            ) 
            ALTER TABLE claro_resource_rights 
            DROP CONSTRAINT IDX_3848F483B87FAB32 ELSE 
            DROP INDEX IDX_3848F483B87FAB32 ON claro_resource_rights
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'resource_rights_unique_resource_role'
            ) 
            ALTER TABLE claro_resource_rights 
            DROP CONSTRAINT resource_rights_unique_resource_role ELSE 
            DROP INDEX resource_rights_unique_resource_role ON claro_resource_rights
        ");
        $this->addSql("
            ALTER TABLE claro_resource_rights 
            ADD CONSTRAINT FK_3848F48389329D25 FOREIGN KEY (resource_id) 
            REFERENCES claro_resource (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_3848F48389329D25 ON claro_resource_rights (resource_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX resource_rights_unique_resource_role ON claro_resource_rights (resource_id, role_id) 
            WHERE resource_id IS NOT NULL 
            AND role_id IS NOT NULL
        ");
        $this->addSql("
            sp_RENAME 'claro_resource_shortcut.resourcenode_id', 
            'node_id', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut 
            DROP COLUMN mime_type
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut ALTER COLUMN node_id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut 
            DROP CONSTRAINT FK_5E7F4AB8B87FAB32
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_5E7F4AB8B87FAB32'
            ) 
            ALTER TABLE claro_resource_shortcut 
            DROP CONSTRAINT IDX_5E7F4AB8B87FAB32 ELSE 
            DROP INDEX IDX_5E7F4AB8B87FAB32 ON claro_resource_shortcut
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut 
            ADD CONSTRAINT FK_5E7F4AB8460D9FD7 FOREIGN KEY (node_id) 
            REFERENCES claro_resource (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_5E7F4AB8460D9FD7 ON claro_resource_shortcut (node_id)
        ");
        $this->addSql("
            sp_RENAME 'claro_text.resourcenode_id', 
            'node_id', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE claro_text 
            DROP COLUMN mime_type
        ");
        $this->addSql("
            ALTER TABLE claro_text ALTER COLUMN node_id INT
        ");
        $this->addSql("
            ALTER TABLE claro_text 
            DROP CONSTRAINT FK_5D9559DCB87FAB32
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'UNIQ_5D9559DCB87FAB32'
            ) 
            ALTER TABLE claro_text 
            DROP CONSTRAINT UNIQ_5D9559DCB87FAB32 ELSE 
            DROP INDEX UNIQ_5D9559DCB87FAB32 ON claro_text
        ");
        $this->addSql("
            ALTER TABLE claro_text 
            ADD CONSTRAINT FK_5D9559DC460D9FD7 FOREIGN KEY (node_id) 
            REFERENCES claro_resource (id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_5D9559DC460D9FD7 ON claro_text (node_id) 
            WHERE node_id IS NOT NULL
        ");
    }
}