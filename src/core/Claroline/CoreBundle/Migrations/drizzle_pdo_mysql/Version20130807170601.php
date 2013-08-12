<?php

namespace Claroline\CoreBundle\Migrations\drizzle_pdo_mysql;

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
            ALTER TABLE claro_resource_rights 
            DROP FOREIGN KEY FK_3848F48389329D25
        ");
        $this->addSql("
            DROP INDEX IDX_3848F48389329D25 ON claro_resource_rights
        ");
        $this->addSql("
            DROP INDEX resource_rights_unique_resource_role ON claro_resource_rights
        ");
        $this->addSql("
            ALTER TABLE claro_resource_rights CHANGE resource_id resourceNode_id INT NOT NULL
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
            DROP mime_type
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            DROP FOREIGN KEY FK_E4A67CAC460D9FD7
        ");
        $this->addSql("
            DROP INDEX UNIQ_E4A67CAC460D9FD7 ON claro_activity
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            ADD mime_type VARCHAR(255) DEFAULT NULL, 
            CHANGE node_id resourceNode_id INT DEFAULT NULL
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
            ALTER TABLE claro_resource_activity 
            DROP FOREIGN KEY FK_DCF37C7E89329D25
        ");
        $this->addSql("
            DROP INDEX IDX_DCF37C7E89329D25 ON claro_resource_activity
        ");
        $this->addSql("
            DROP INDEX resource_activity_unique_combination ON claro_resource_activity
        ");
        $this->addSql("
            ALTER TABLE claro_resource_activity CHANGE resource_id resourceNode_id INT NOT NULL
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
            DROP FOREIGN KEY FK_EA81C80B460D9FD7
        ");
        $this->addSql("
            DROP INDEX UNIQ_EA81C80B460D9FD7 ON claro_file
        ");
        $this->addSql("
            ALTER TABLE claro_file 
            ADD mime_type VARCHAR(255) DEFAULT NULL, 
            CHANGE node_id resourceNode_id INT DEFAULT NULL
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
            DROP FOREIGN KEY FK_50B267EA460D9FD7
        ");
        $this->addSql("
            DROP INDEX UNIQ_50B267EA460D9FD7 ON claro_link
        ");
        $this->addSql("
            ALTER TABLE claro_link 
            ADD mime_type VARCHAR(255) DEFAULT NULL, 
            CHANGE node_id resourceNode_id INT DEFAULT NULL
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
            DROP FOREIGN KEY FK_12EEC186460D9FD7
        ");
        $this->addSql("
            DROP INDEX UNIQ_12EEC186460D9FD7 ON claro_directory
        ");
        $this->addSql("
            ALTER TABLE claro_directory 
            ADD mime_type VARCHAR(255) DEFAULT NULL, 
            CHANGE node_id resourceNode_id INT DEFAULT NULL
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
            DROP FOREIGN KEY FK_5E7F4AB8460D9FD7
        ");
        $this->addSql("
            DROP INDEX IDX_5E7F4AB8460D9FD7 ON claro_resource_shortcut
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut 
            ADD mime_type VARCHAR(255) DEFAULT NULL, 
            CHANGE node_id resourceNode_id INT NOT NULL
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
            DROP FOREIGN KEY FK_5D9559DC460D9FD7
        ");
        $this->addSql("
            DROP INDEX UNIQ_5D9559DC460D9FD7 ON claro_text
        ");
        $this->addSql("
            ALTER TABLE claro_text 
            ADD mime_type VARCHAR(255) DEFAULT NULL, 
            CHANGE node_id resourceNode_id INT DEFAULT NULL
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
            DROP FOREIGN KEY FK_E4A67CACB87FAB32
        ");
        $this->addSql("
            DROP INDEX UNIQ_E4A67CACB87FAB32 ON claro_activity
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            DROP mime_type, 
            CHANGE resourcenode_id node_id INT DEFAULT NULL
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
            DROP FOREIGN KEY FK_12EEC186B87FAB32
        ");
        $this->addSql("
            DROP INDEX UNIQ_12EEC186B87FAB32 ON claro_directory
        ");
        $this->addSql("
            ALTER TABLE claro_directory 
            DROP mime_type, 
            CHANGE resourcenode_id node_id INT DEFAULT NULL
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
            DROP FOREIGN KEY FK_EA81C80BB87FAB32
        ");
        $this->addSql("
            DROP INDEX UNIQ_EA81C80BB87FAB32 ON claro_file
        ");
        $this->addSql("
            ALTER TABLE claro_file 
            DROP mime_type, 
            CHANGE resourcenode_id node_id INT DEFAULT NULL
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
            DROP FOREIGN KEY FK_50B267EAB87FAB32
        ");
        $this->addSql("
            DROP INDEX UNIQ_50B267EAB87FAB32 ON claro_link
        ");
        $this->addSql("
            ALTER TABLE claro_link 
            DROP mime_type, 
            CHANGE resourcenode_id node_id INT DEFAULT NULL
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
            ADD mime_type VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_activity 
            DROP FOREIGN KEY FK_DCF37C7EB87FAB32
        ");
        $this->addSql("
            DROP INDEX IDX_DCF37C7EB87FAB32 ON claro_resource_activity
        ");
        $this->addSql("
            DROP INDEX resource_activity_unique_combination ON claro_resource_activity
        ");
        $this->addSql("
            ALTER TABLE claro_resource_activity CHANGE resourcenode_id resource_id INT NOT NULL
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
            ALTER TABLE claro_resource_rights 
            DROP FOREIGN KEY FK_3848F483B87FAB32
        ");
        $this->addSql("
            DROP INDEX IDX_3848F483B87FAB32 ON claro_resource_rights
        ");
        $this->addSql("
            DROP INDEX resource_rights_unique_resource_role ON claro_resource_rights
        ");
        $this->addSql("
            ALTER TABLE claro_resource_rights CHANGE resourcenode_id resource_id INT NOT NULL
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
            DROP FOREIGN KEY FK_5E7F4AB8B87FAB32
        ");
        $this->addSql("
            DROP INDEX IDX_5E7F4AB8B87FAB32 ON claro_resource_shortcut
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut 
            DROP mime_type, 
            CHANGE resourcenode_id node_id INT NOT NULL
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
            DROP FOREIGN KEY FK_5D9559DCB87FAB32
        ");
        $this->addSql("
            DROP INDEX UNIQ_5D9559DCB87FAB32 ON claro_text
        ");
        $this->addSql("
            ALTER TABLE claro_text 
            DROP mime_type, 
            CHANGE resourcenode_id node_id INT DEFAULT NULL
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