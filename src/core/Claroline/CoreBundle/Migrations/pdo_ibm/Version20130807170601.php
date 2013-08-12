<?php

namespace Claroline\CoreBundle\Migrations\pdo_ibm;

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
            ALTER TABLE claro_resource_rights RENAME resource_id TO resourceNode_id
        ");
        $this->addSql("
            ALTER TABLE claro_resource_rights 
            DROP FOREIGN KEY FK_3848F48389329D25
        ");
        $this->addSql("
            DROP INDEX IDX_3848F48389329D25
        ");
        $this->addSql("
            DROP INDEX resource_rights_unique_resource_role
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
        ");
        $this->addSql("
            ALTER TABLE claro_resource 
            DROP COLUMN mime_type
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            ADD COLUMN mime_type VARCHAR(255) DEFAULT NULL RENAME node_id TO resourceNode_id
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            DROP FOREIGN KEY FK_E4A67CAC460D9FD7
        ");
        $this->addSql("
            DROP INDEX UNIQ_E4A67CAC460D9FD7
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            ADD CONSTRAINT FK_E4A67CACB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource (id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_E4A67CACB87FAB32 ON claro_activity (resourceNode_id)
        ");
        $this->addSql("
            ALTER TABLE claro_resource_activity RENAME resource_id TO resourceNode_id
        ");
        $this->addSql("
            ALTER TABLE claro_resource_activity 
            DROP FOREIGN KEY FK_DCF37C7E89329D25
        ");
        $this->addSql("
            DROP INDEX IDX_DCF37C7E89329D25
        ");
        $this->addSql("
            DROP INDEX resource_activity_unique_combination
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
        ");
        $this->addSql("
            ALTER TABLE claro_file 
            ADD COLUMN mime_type VARCHAR(255) DEFAULT NULL RENAME node_id TO resourceNode_id
        ");
        $this->addSql("
            ALTER TABLE claro_file 
            DROP FOREIGN KEY FK_EA81C80B460D9FD7
        ");
        $this->addSql("
            DROP INDEX UNIQ_EA81C80B460D9FD7
        ");
        $this->addSql("
            ALTER TABLE claro_file 
            ADD CONSTRAINT FK_EA81C80BB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource (id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_EA81C80BB87FAB32 ON claro_file (resourceNode_id)
        ");
        $this->addSql("
            ALTER TABLE claro_link 
            ADD COLUMN mime_type VARCHAR(255) DEFAULT NULL RENAME node_id TO resourceNode_id
        ");
        $this->addSql("
            ALTER TABLE claro_link 
            DROP FOREIGN KEY FK_50B267EA460D9FD7
        ");
        $this->addSql("
            DROP INDEX UNIQ_50B267EA460D9FD7
        ");
        $this->addSql("
            ALTER TABLE claro_link 
            ADD CONSTRAINT FK_50B267EAB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource (id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_50B267EAB87FAB32 ON claro_link (resourceNode_id)
        ");
        $this->addSql("
            ALTER TABLE claro_directory 
            ADD COLUMN mime_type VARCHAR(255) DEFAULT NULL RENAME node_id TO resourceNode_id
        ");
        $this->addSql("
            ALTER TABLE claro_directory 
            DROP FOREIGN KEY FK_12EEC186460D9FD7
        ");
        $this->addSql("
            DROP INDEX UNIQ_12EEC186460D9FD7
        ");
        $this->addSql("
            ALTER TABLE claro_directory 
            ADD CONSTRAINT FK_12EEC186B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource (id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_12EEC186B87FAB32 ON claro_directory (resourceNode_id)
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut 
            ADD COLUMN mime_type VARCHAR(255) DEFAULT NULL RENAME node_id TO resourceNode_id
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut 
            DROP FOREIGN KEY FK_5E7F4AB8460D9FD7
        ");
        $this->addSql("
            DROP INDEX IDX_5E7F4AB8460D9FD7
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
            ALTER TABLE claro_text 
            ADD COLUMN mime_type VARCHAR(255) DEFAULT NULL RENAME node_id TO resourceNode_id
        ");
        $this->addSql("
            ALTER TABLE claro_text 
            DROP FOREIGN KEY FK_5D9559DC460D9FD7
        ");
        $this->addSql("
            DROP INDEX UNIQ_5D9559DC460D9FD7
        ");
        $this->addSql("
            ALTER TABLE claro_text 
            ADD CONSTRAINT FK_5D9559DCB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource (id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_5D9559DCB87FAB32 ON claro_text (resourceNode_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_activity 
            DROP COLUMN mime_type RENAME resourcenode_id TO node_id
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            DROP FOREIGN KEY FK_E4A67CACB87FAB32
        ");
        $this->addSql("
            DROP INDEX UNIQ_E4A67CACB87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            ADD CONSTRAINT FK_E4A67CAC460D9FD7 FOREIGN KEY (node_id) 
            REFERENCES claro_resource (id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_E4A67CAC460D9FD7 ON claro_activity (node_id)
        ");
        $this->addSql("
            ALTER TABLE claro_directory 
            DROP COLUMN mime_type RENAME resourcenode_id TO node_id
        ");
        $this->addSql("
            ALTER TABLE claro_directory 
            DROP FOREIGN KEY FK_12EEC186B87FAB32
        ");
        $this->addSql("
            DROP INDEX UNIQ_12EEC186B87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_directory 
            ADD CONSTRAINT FK_12EEC186460D9FD7 FOREIGN KEY (node_id) 
            REFERENCES claro_resource (id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_12EEC186460D9FD7 ON claro_directory (node_id)
        ");
        $this->addSql("
            ALTER TABLE claro_file 
            DROP COLUMN mime_type RENAME resourcenode_id TO node_id
        ");
        $this->addSql("
            ALTER TABLE claro_file 
            DROP FOREIGN KEY FK_EA81C80BB87FAB32
        ");
        $this->addSql("
            DROP INDEX UNIQ_EA81C80BB87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_file 
            ADD CONSTRAINT FK_EA81C80B460D9FD7 FOREIGN KEY (node_id) 
            REFERENCES claro_resource (id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_EA81C80B460D9FD7 ON claro_file (node_id)
        ");
        $this->addSql("
            ALTER TABLE claro_link 
            DROP COLUMN mime_type RENAME resourcenode_id TO node_id
        ");
        $this->addSql("
            ALTER TABLE claro_link 
            DROP FOREIGN KEY FK_50B267EAB87FAB32
        ");
        $this->addSql("
            DROP INDEX UNIQ_50B267EAB87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_link 
            ADD CONSTRAINT FK_50B267EA460D9FD7 FOREIGN KEY (node_id) 
            REFERENCES claro_resource (id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_50B267EA460D9FD7 ON claro_link (node_id)
        ");
        $this->addSql("
            ALTER TABLE claro_resource 
            ADD COLUMN mime_type VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_activity RENAME resourcenode_id TO resource_id
        ");
        $this->addSql("
            ALTER TABLE claro_resource_activity 
            DROP FOREIGN KEY FK_DCF37C7EB87FAB32
        ");
        $this->addSql("
            DROP INDEX IDX_DCF37C7EB87FAB32
        ");
        $this->addSql("
            DROP INDEX resource_activity_unique_combination
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
        ");
        $this->addSql("
            ALTER TABLE claro_resource_rights RENAME resourcenode_id TO resource_id
        ");
        $this->addSql("
            ALTER TABLE claro_resource_rights 
            DROP FOREIGN KEY FK_3848F483B87FAB32
        ");
        $this->addSql("
            DROP INDEX IDX_3848F483B87FAB32
        ");
        $this->addSql("
            DROP INDEX resource_rights_unique_resource_role
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
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut 
            DROP COLUMN mime_type RENAME resourcenode_id TO node_id
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut 
            DROP FOREIGN KEY FK_5E7F4AB8B87FAB32
        ");
        $this->addSql("
            DROP INDEX IDX_5E7F4AB8B87FAB32
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
            DROP COLUMN mime_type RENAME resourcenode_id TO node_id
        ");
        $this->addSql("
            ALTER TABLE claro_text 
            DROP FOREIGN KEY FK_5D9559DCB87FAB32
        ");
        $this->addSql("
            DROP INDEX UNIQ_5D9559DCB87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_text 
            ADD CONSTRAINT FK_5D9559DC460D9FD7 FOREIGN KEY (node_id) 
            REFERENCES claro_resource (id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_5D9559DC460D9FD7 ON claro_text (node_id)
        ");
    }
}