<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/08/09 09:18:08
 */
class Version20130809091808 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_resource_node (
                id INT AUTO_INCREMENT NOT NULL, 
                license_id INT DEFAULT NULL, 
                resource_type_id INT NOT NULL, 
                user_id INT NOT NULL, 
                icon_id INT DEFAULT NULL, 
                parent_id INT DEFAULT NULL, 
                workspace_id INT NOT NULL, 
                next_id INT DEFAULT NULL, 
                previous_id INT DEFAULT NULL, 
                creation_date DATETIME NOT NULL, 
                modification_date DATETIME NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                lvl INT DEFAULT NULL, 
                path VARCHAR(3000) DEFAULT NULL, 
                mime_type VARCHAR(255) DEFAULT NULL, 
                INDEX IDX_A76799FF460F904B (license_id), 
                INDEX IDX_A76799FF98EC6B7B (resource_type_id), 
                INDEX IDX_A76799FFA76ED395 (user_id), 
                INDEX IDX_A76799FF54B9D732 (icon_id), 
                INDEX IDX_A76799FF727ACA70 (parent_id), 
                INDEX IDX_A76799FF82D40A1F (workspace_id), 
                UNIQUE INDEX UNIQ_A76799FFAA23F6C8 (next_id), 
                UNIQUE INDEX UNIQ_A76799FF2DE62210 (previous_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
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
            ADD CONSTRAINT FK_A76799FFA76ED395 FOREIGN KEY (user_id) 
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
            ALTER TABLE claro_resource_rights 
            DROP FOREIGN KEY FK_3848F483B87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_resource_rights 
            ADD CONSTRAINT FK_3848F483B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            DROP FOREIGN KEY FK_E4A67CACB87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            ADD CONSTRAINT FK_E4A67CACB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id)
        ");
        $this->addSql("
            ALTER TABLE claro_resource_activity 
            DROP FOREIGN KEY FK_DCF37C7EB87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_resource_activity 
            ADD CONSTRAINT FK_DCF37C7EB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_file 
            DROP FOREIGN KEY FK_EA81C80BB87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_file 
            ADD CONSTRAINT FK_EA81C80BB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id)
        ");
        $this->addSql("
            ALTER TABLE claro_link 
            DROP FOREIGN KEY FK_50B267EAB87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_link 
            ADD CONSTRAINT FK_50B267EAB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id)
        ");
        $this->addSql("
            ALTER TABLE claro_directory 
            DROP FOREIGN KEY FK_12EEC186B87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_directory 
            ADD CONSTRAINT FK_12EEC186B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id)
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut 
            DROP FOREIGN KEY FK_5E7F4AB8B87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut 
            ADD CONSTRAINT FK_5E7F4AB8B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_text 
            DROP FOREIGN KEY FK_5D9559DCB87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_text 
            ADD CONSTRAINT FK_5D9559DCB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id)
        ");
        $this->addSql("
            ALTER TABLE claro_log 
            DROP FOREIGN KEY FK_97FAB91FB87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_log 
            ADD CONSTRAINT FK_97FAB91FB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE SET NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_resource_node 
            DROP FOREIGN KEY FK_A76799FF727ACA70
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            DROP FOREIGN KEY FK_A76799FFAA23F6C8
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            DROP FOREIGN KEY FK_A76799FF2DE62210
        ");
        $this->addSql("
            ALTER TABLE claro_resource_rights 
            DROP FOREIGN KEY FK_3848F483B87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            DROP FOREIGN KEY FK_E4A67CACB87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_resource_activity 
            DROP FOREIGN KEY FK_DCF37C7EB87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_file 
            DROP FOREIGN KEY FK_EA81C80BB87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_link 
            DROP FOREIGN KEY FK_50B267EAB87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_directory 
            DROP FOREIGN KEY FK_12EEC186B87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut 
            DROP FOREIGN KEY FK_5E7F4AB8B87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_text 
            DROP FOREIGN KEY FK_5D9559DCB87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_log 
            DROP FOREIGN KEY FK_97FAB91FB87FAB32
        ");
        $this->addSql("
            DROP TABLE claro_resource_node
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            DROP FOREIGN KEY FK_E4A67CACB87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            ADD CONSTRAINT FK_E4A67CACB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource (id)
        ");
        $this->addSql("
            ALTER TABLE claro_directory 
            DROP FOREIGN KEY FK_12EEC186B87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_directory 
            ADD CONSTRAINT FK_12EEC186B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource (id)
        ");
        $this->addSql("
            ALTER TABLE claro_file 
            DROP FOREIGN KEY FK_EA81C80BB87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_file 
            ADD CONSTRAINT FK_EA81C80BB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource (id)
        ");
        $this->addSql("
            ALTER TABLE claro_link 
            DROP FOREIGN KEY FK_50B267EAB87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_link 
            ADD CONSTRAINT FK_50B267EAB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource (id)
        ");
        $this->addSql("
            ALTER TABLE claro_log 
            DROP FOREIGN KEY FK_97FAB91FB87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_log 
            ADD CONSTRAINT FK_97FAB91FB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_activity 
            DROP FOREIGN KEY FK_DCF37C7EB87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_resource_activity 
            ADD CONSTRAINT FK_DCF37C7EB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_resource_rights 
            DROP FOREIGN KEY FK_3848F483B87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_resource_rights 
            ADD CONSTRAINT FK_3848F483B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut 
            DROP FOREIGN KEY FK_5E7F4AB8B87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut 
            ADD CONSTRAINT FK_5E7F4AB8B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_text 
            DROP FOREIGN KEY FK_5D9559DCB87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_text 
            ADD CONSTRAINT FK_5D9559DCB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource (id)
        ");
    }
}