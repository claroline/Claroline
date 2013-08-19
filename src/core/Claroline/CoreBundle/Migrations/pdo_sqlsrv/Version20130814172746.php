<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/08/14 05:27:46
 */
class Version20130814172746 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_resource_node (
                id INT IDENTITY NOT NULL, 
                license_id INT, 
                resource_type_id INT NOT NULL, 
                creator_id INT NOT NULL, 
                icon_id INT, 
                parent_id INT, 
                workspace_id INT NOT NULL, 
                next_id INT, 
                previous_id INT, 
                creation_date DATETIME2(6) NOT NULL, 
                modification_date DATETIME2(6) NOT NULL, 
                name NVARCHAR(255) NOT NULL, 
                lvl INT, 
                path NVARCHAR(3000), 
                mime_type NVARCHAR(255), 
                class NVARCHAR(256) NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_A76799FF460F904B ON claro_resource_node (license_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A76799FF98EC6B7B ON claro_resource_node (resource_type_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A76799FF61220EA6 ON claro_resource_node (creator_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A76799FF54B9D732 ON claro_resource_node (icon_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A76799FF727ACA70 ON claro_resource_node (parent_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A76799FF82D40A1F ON claro_resource_node (workspace_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_A76799FFAA23F6C8 ON claro_resource_node (next_id) 
            WHERE next_id IS NOT NULL
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_A76799FF2DE62210 ON claro_resource_node (previous_id) 
            WHERE previous_id IS NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            ADD CONSTRAINT FK_A76799FF460F904B FOREIGN KEY (license_id) 
            REFERENCES claro_license (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            ADD CONSTRAINT FK_A76799FF98EC6B7B FOREIGN KEY (resource_type_id) 
            REFERENCES claro_resource_type (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            ADD CONSTRAINT FK_A76799FF61220EA6 FOREIGN KEY (creator_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            ADD CONSTRAINT FK_A76799FF54B9D732 FOREIGN KEY (icon_id) 
            REFERENCES claro_resource_icon (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            ADD CONSTRAINT FK_A76799FF727ACA70 FOREIGN KEY (parent_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            ADD CONSTRAINT FK_A76799FF82D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            ADD CONSTRAINT FK_A76799FFAA23F6C8 FOREIGN KEY (next_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            ADD CONSTRAINT FK_A76799FF2DE62210 FOREIGN KEY (previous_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE SET NULL
        ");
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
            REFERENCES claro_resource_node (id) 
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
            ALTER TABLE claro_resource_type 
            DROP COLUMN parent_id
        ");
        $this->addSql("
            ALTER TABLE claro_resource_type 
            DROP COLUMN class
        ");
        $this->addSql("
            ALTER TABLE claro_resource_type 
            DROP CONSTRAINT FK_AEC62693727ACA70
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_AEC62693727ACA70'
            ) 
            ALTER TABLE claro_resource_type 
            DROP CONSTRAINT IDX_AEC62693727ACA70 ELSE 
            DROP INDEX IDX_AEC62693727ACA70 ON claro_resource_type
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            ADD resourceNode_id INT
        ");
        $this->addSql("
            ALTER TABLE claro_activity ALTER COLUMN id INT IDENTITY NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            DROP CONSTRAINT FK_E4A67CACBF396750
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            ADD CONSTRAINT FK_E4A67CACB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id)
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
            REFERENCES claro_resource_node (id) 
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
            ALTER TABLE claro_file 
            ADD resourceNode_id INT
        ");
        $this->addSql("
            ALTER TABLE claro_file ALTER COLUMN id INT IDENTITY NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_file ALTER COLUMN hash_name NVARCHAR(50) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_file 
            DROP CONSTRAINT FK_EA81C80BBF396750
        ");
        $this->addSql("
            ALTER TABLE claro_file 
            ADD CONSTRAINT FK_EA81C80BB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_EA81C80BB87FAB32 ON claro_file (resourceNode_id) 
            WHERE resourceNode_id IS NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_link 
            ADD resourceNode_id INT
        ");
        $this->addSql("
            ALTER TABLE claro_link ALTER COLUMN id INT IDENTITY NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_link 
            DROP CONSTRAINT FK_50B267EABF396750
        ");
        $this->addSql("
            ALTER TABLE claro_link 
            ADD CONSTRAINT FK_50B267EAB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_50B267EAB87FAB32 ON claro_link (resourceNode_id) 
            WHERE resourceNode_id IS NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_directory 
            ADD resourceNode_id INT
        ");
        $this->addSql("
            ALTER TABLE claro_directory ALTER COLUMN id INT IDENTITY NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_directory 
            DROP CONSTRAINT FK_12EEC186BF396750
        ");
        $this->addSql("
            ALTER TABLE claro_directory 
            ADD CONSTRAINT FK_12EEC186B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_12EEC186B87FAB32 ON claro_directory (resourceNode_id) 
            WHERE resourceNode_id IS NOT NULL
        ");
        $this->addSql("
            sp_RENAME 'claro_resource_shortcut.resource_id', 
            'target_id', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut 
            ADD resourceNode_id INT
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut ALTER COLUMN id INT IDENTITY NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut ALTER COLUMN target_id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut 
            DROP CONSTRAINT FK_5E7F4AB8BF396750
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut 
            DROP CONSTRAINT FK_5E7F4AB889329D25
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_5E7F4AB889329D25'
            ) 
            ALTER TABLE claro_resource_shortcut 
            DROP CONSTRAINT IDX_5E7F4AB889329D25 ELSE 
            DROP INDEX IDX_5E7F4AB889329D25 ON claro_resource_shortcut
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut 
            ADD CONSTRAINT FK_5E7F4AB8158E0B66 FOREIGN KEY (target_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut 
            ADD CONSTRAINT FK_5E7F4AB8B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id)
        ");
        $this->addSql("
            CREATE INDEX IDX_5E7F4AB8158E0B66 ON claro_resource_shortcut (target_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_5E7F4AB8B87FAB32 ON claro_resource_shortcut (resourceNode_id) 
            WHERE resourceNode_id IS NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_text 
            ADD resourceNode_id INT
        ");
        $this->addSql("
            ALTER TABLE claro_text ALTER COLUMN id INT IDENTITY NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_text 
            DROP CONSTRAINT FK_5D9559DCBF396750
        ");
        $this->addSql("
            ALTER TABLE claro_text 
            ADD CONSTRAINT FK_5D9559DCB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_5D9559DCB87FAB32 ON claro_text (resourceNode_id) 
            WHERE resourceNode_id IS NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_text_revision ALTER COLUMN content VARCHAR(MAX) NOT NULL
        ");
        $this->addSql("
            sp_RENAME 'claro_log.resource_id', 
            'resourceNode_id', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE claro_log ALTER COLUMN resourceNode_id INT
        ");
        $this->addSql("
            ALTER TABLE claro_log 
            DROP CONSTRAINT FK_97FAB91F89329D25
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_97FAB91F89329D25'
            ) 
            ALTER TABLE claro_log 
            DROP CONSTRAINT IDX_97FAB91F89329D25 ELSE 
            DROP INDEX IDX_97FAB91F89329D25 ON claro_log
        ");
        $this->addSql("
            ALTER TABLE claro_log 
            ADD CONSTRAINT FK_97FAB91FB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            CREATE INDEX IDX_97FAB91FB87FAB32 ON claro_log (resourceNode_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_resource_node 
            DROP CONSTRAINT FK_A76799FF727ACA70
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            DROP CONSTRAINT FK_A76799FFAA23F6C8
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            DROP CONSTRAINT FK_A76799FF2DE62210
        ");
        $this->addSql("
            ALTER TABLE claro_resource_rights 
            DROP CONSTRAINT FK_3848F483B87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            DROP CONSTRAINT FK_E4A67CACB87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_resource_activity 
            DROP CONSTRAINT FK_DCF37C7EB87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_file 
            DROP CONSTRAINT FK_EA81C80BB87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_link 
            DROP CONSTRAINT FK_50B267EAB87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_directory 
            DROP CONSTRAINT FK_12EEC186B87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut 
            DROP CONSTRAINT FK_5E7F4AB8158E0B66
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut 
            DROP CONSTRAINT FK_5E7F4AB8B87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_text 
            DROP CONSTRAINT FK_5D9559DCB87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_log 
            DROP CONSTRAINT FK_97FAB91FB87FAB32
        ");
        $this->addSql("
            DROP TABLE claro_resource_node
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            DROP COLUMN resourceNode_id
        ");
        $this->addSql("
            ALTER TABLE claro_activity ALTER COLUMN id INT NOT NULL
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
            ADD CONSTRAINT FK_E4A67CACBF396750 FOREIGN KEY (id) 
            REFERENCES claro_resource (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_directory 
            DROP COLUMN resourceNode_id
        ");
        $this->addSql("
            ALTER TABLE claro_directory ALTER COLUMN id INT NOT NULL
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
            ADD CONSTRAINT FK_12EEC186BF396750 FOREIGN KEY (id) 
            REFERENCES claro_resource (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_file 
            DROP COLUMN resourceNode_id
        ");
        $this->addSql("
            ALTER TABLE claro_file ALTER COLUMN id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_file ALTER COLUMN hash_name NVARCHAR(36) NOT NULL
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
            ADD CONSTRAINT FK_EA81C80BBF396750 FOREIGN KEY (id) 
            REFERENCES claro_resource (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_link 
            DROP COLUMN resourceNode_id
        ");
        $this->addSql("
            ALTER TABLE claro_link ALTER COLUMN id INT NOT NULL
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
            ADD CONSTRAINT FK_50B267EABF396750 FOREIGN KEY (id) 
            REFERENCES claro_resource (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            sp_RENAME 'claro_log.resourcenode_id', 
            'resource_id', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE claro_log ALTER COLUMN resource_id INT
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_97FAB91FB87FAB32'
            ) 
            ALTER TABLE claro_log 
            DROP CONSTRAINT IDX_97FAB91FB87FAB32 ELSE 
            DROP INDEX IDX_97FAB91FB87FAB32 ON claro_log
        ");
        $this->addSql("
            ALTER TABLE claro_log 
            ADD CONSTRAINT FK_97FAB91F89329D25 FOREIGN KEY (resource_id) 
            REFERENCES claro_resource (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            CREATE INDEX IDX_97FAB91F89329D25 ON claro_log (resource_id)
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
            sp_RENAME 'claro_resource_shortcut.target_id', 
            'resource_id', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut 
            DROP COLUMN resourceNode_id
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut ALTER COLUMN id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut ALTER COLUMN resource_id INT NOT NULL
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_5E7F4AB8158E0B66'
            ) 
            ALTER TABLE claro_resource_shortcut 
            DROP CONSTRAINT IDX_5E7F4AB8158E0B66 ELSE 
            DROP INDEX IDX_5E7F4AB8158E0B66 ON claro_resource_shortcut
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'UNIQ_5E7F4AB8B87FAB32'
            ) 
            ALTER TABLE claro_resource_shortcut 
            DROP CONSTRAINT UNIQ_5E7F4AB8B87FAB32 ELSE 
            DROP INDEX UNIQ_5E7F4AB8B87FAB32 ON claro_resource_shortcut
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut 
            ADD CONSTRAINT FK_5E7F4AB8BF396750 FOREIGN KEY (id) 
            REFERENCES claro_resource (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut 
            ADD CONSTRAINT FK_5E7F4AB889329D25 FOREIGN KEY (resource_id) 
            REFERENCES claro_resource (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_5E7F4AB889329D25 ON claro_resource_shortcut (resource_id)
        ");
        $this->addSql("
            ALTER TABLE claro_resource_type 
            ADD parent_id INT
        ");
        $this->addSql("
            ALTER TABLE claro_resource_type 
            ADD class NVARCHAR(255)
        ");
        $this->addSql("
            ALTER TABLE claro_resource_type 
            ADD CONSTRAINT FK_AEC62693727ACA70 FOREIGN KEY (parent_id) 
            REFERENCES claro_resource_type (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            CREATE INDEX IDX_AEC62693727ACA70 ON claro_resource_type (parent_id)
        ");
        $this->addSql("
            ALTER TABLE claro_text 
            DROP COLUMN resourceNode_id
        ");
        $this->addSql("
            ALTER TABLE claro_text ALTER COLUMN id INT NOT NULL
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
            ADD CONSTRAINT FK_5D9559DCBF396750 FOREIGN KEY (id) 
            REFERENCES claro_resource (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_text_revision ALTER COLUMN content NVARCHAR(255) NOT NULL
        ");
    }
}