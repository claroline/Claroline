<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/08/07 04:30:20
 */
class Version20130807163019 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_resource 
            DROP COLUMN discr
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            ADD node_id INT
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
            ADD CONSTRAINT FK_E4A67CAC460D9FD7 FOREIGN KEY (node_id) 
            REFERENCES claro_resource (id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_E4A67CAC460D9FD7 ON claro_activity (node_id) 
            WHERE node_id IS NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_file 
            ADD node_id INT
        ");
        $this->addSql("
            ALTER TABLE claro_file ALTER COLUMN id INT IDENTITY NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_file 
            DROP CONSTRAINT FK_EA81C80BBF396750
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
            ALTER TABLE claro_link 
            ADD node_id INT
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
            ADD CONSTRAINT FK_50B267EA460D9FD7 FOREIGN KEY (node_id) 
            REFERENCES claro_resource (id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_50B267EA460D9FD7 ON claro_link (node_id) 
            WHERE node_id IS NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_directory 
            ADD node_id INT
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
            ADD CONSTRAINT FK_12EEC186460D9FD7 FOREIGN KEY (node_id) 
            REFERENCES claro_resource (id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_12EEC186460D9FD7 ON claro_directory (node_id) 
            WHERE node_id IS NOT NULL
        ");
        $this->addSql("
            sp_RENAME 'claro_resource_shortcut.resource_id', 
            'node_id', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut ALTER COLUMN node_id INT NOT NULL
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
            ADD CONSTRAINT FK_5E7F4AB8460D9FD7 FOREIGN KEY (node_id) 
            REFERENCES claro_resource (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_5E7F4AB8460D9FD7 ON claro_resource_shortcut (node_id)
        ");
        $this->addSql("
            ALTER TABLE claro_text 
            ADD node_id INT
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
            ADD CONSTRAINT FK_5D9559DC460D9FD7 FOREIGN KEY (node_id) 
            REFERENCES claro_resource (id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_5D9559DC460D9FD7 ON claro_text (node_id) 
            WHERE node_id IS NOT NULL
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
            REFERENCES claro_resource (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            CREATE INDEX IDX_97FAB91FB87FAB32 ON claro_log (resourceNode_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_activity 
            DROP COLUMN node_id
        ");
        $this->addSql("
            ALTER TABLE claro_activity ALTER COLUMN id INT NOT NULL
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
            ADD CONSTRAINT FK_E4A67CACBF396750 FOREIGN KEY (id) 
            REFERENCES claro_resource (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_directory 
            DROP COLUMN node_id
        ");
        $this->addSql("
            ALTER TABLE claro_directory ALTER COLUMN id INT NOT NULL
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
            ADD CONSTRAINT FK_12EEC186BF396750 FOREIGN KEY (id) 
            REFERENCES claro_resource (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_file 
            DROP COLUMN node_id
        ");
        $this->addSql("
            ALTER TABLE claro_file ALTER COLUMN id INT NOT NULL
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
            ADD CONSTRAINT FK_EA81C80BBF396750 FOREIGN KEY (id) 
            REFERENCES claro_resource (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_link 
            DROP COLUMN node_id
        ");
        $this->addSql("
            ALTER TABLE claro_link ALTER COLUMN id INT NOT NULL
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
            ALTER TABLE claro_log 
            DROP CONSTRAINT FK_97FAB91FB87FAB32
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
            ALTER TABLE claro_resource 
            ADD discr NVARCHAR(255) NOT NULL
        ");
        $this->addSql("
            sp_RENAME 'claro_resource_shortcut.node_id', 
            'resource_id', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut ALTER COLUMN resource_id INT NOT NULL
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
            ALTER TABLE claro_text 
            DROP COLUMN node_id
        ");
        $this->addSql("
            ALTER TABLE claro_text ALTER COLUMN id INT NOT NULL
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
            ADD CONSTRAINT FK_5D9559DCBF396750 FOREIGN KEY (id) 
            REFERENCES claro_resource (id) 
            ON DELETE CASCADE
        ");
    }
}