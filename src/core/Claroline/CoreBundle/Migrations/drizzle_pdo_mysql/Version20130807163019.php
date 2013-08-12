<?php

namespace Claroline\CoreBundle\Migrations\drizzle_pdo_mysql;

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
            DROP discr
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            DROP FOREIGN KEY FK_E4A67CACBF396750
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            ADD node_id INT DEFAULT NULL, 
            CHANGE id id INT AUTO_INCREMENT NOT NULL
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
            ALTER TABLE claro_file 
            DROP FOREIGN KEY FK_EA81C80BBF396750
        ");
        $this->addSql("
            ALTER TABLE claro_file 
            ADD node_id INT DEFAULT NULL, 
            CHANGE id id INT AUTO_INCREMENT NOT NULL
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
            DROP FOREIGN KEY FK_50B267EABF396750
        ");
        $this->addSql("
            ALTER TABLE claro_link 
            ADD node_id INT DEFAULT NULL, 
            CHANGE id id INT AUTO_INCREMENT NOT NULL
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
            ALTER TABLE claro_directory 
            DROP FOREIGN KEY FK_12EEC186BF396750
        ");
        $this->addSql("
            ALTER TABLE claro_directory 
            ADD node_id INT DEFAULT NULL, 
            CHANGE id id INT AUTO_INCREMENT NOT NULL
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
            ALTER TABLE claro_resource_shortcut 
            DROP FOREIGN KEY FK_5E7F4AB8BF396750
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut 
            DROP FOREIGN KEY FK_5E7F4AB889329D25
        ");
        $this->addSql("
            DROP INDEX IDX_5E7F4AB889329D25 ON claro_resource_shortcut
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut CHANGE resource_id node_id INT NOT NULL
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
            DROP FOREIGN KEY FK_5D9559DCBF396750
        ");
        $this->addSql("
            ALTER TABLE claro_text 
            ADD node_id INT DEFAULT NULL, 
            CHANGE id id INT AUTO_INCREMENT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_text 
            ADD CONSTRAINT FK_5D9559DC460D9FD7 FOREIGN KEY (node_id) 
            REFERENCES claro_resource (id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_5D9559DC460D9FD7 ON claro_text (node_id)
        ");
        $this->addSql("
            ALTER TABLE claro_log 
            DROP FOREIGN KEY FK_97FAB91F89329D25
        ");
        $this->addSql("
            DROP INDEX IDX_97FAB91F89329D25 ON claro_log
        ");
        $this->addSql("
            ALTER TABLE claro_log CHANGE resource_id resourceNode_id INT DEFAULT NULL
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
            DROP FOREIGN KEY FK_E4A67CAC460D9FD7
        ");
        $this->addSql("
            DROP INDEX UNIQ_E4A67CAC460D9FD7 ON claro_activity
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            DROP node_id, 
            CHANGE id id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            ADD CONSTRAINT FK_E4A67CACBF396750 FOREIGN KEY (id) 
            REFERENCES claro_resource (id) 
            ON DELETE CASCADE
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
            DROP node_id, 
            CHANGE id id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_directory 
            ADD CONSTRAINT FK_12EEC186BF396750 FOREIGN KEY (id) 
            REFERENCES claro_resource (id) 
            ON DELETE CASCADE
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
            DROP node_id, 
            CHANGE id id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_file 
            ADD CONSTRAINT FK_EA81C80BBF396750 FOREIGN KEY (id) 
            REFERENCES claro_resource (id) 
            ON DELETE CASCADE
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
            DROP node_id, 
            CHANGE id id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_link 
            ADD CONSTRAINT FK_50B267EABF396750 FOREIGN KEY (id) 
            REFERENCES claro_resource (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_log 
            DROP FOREIGN KEY FK_97FAB91FB87FAB32
        ");
        $this->addSql("
            DROP INDEX IDX_97FAB91FB87FAB32 ON claro_log
        ");
        $this->addSql("
            ALTER TABLE claro_log CHANGE resourcenode_id resource_id INT DEFAULT NULL
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
            ADD discr VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut 
            DROP FOREIGN KEY FK_5E7F4AB8460D9FD7
        ");
        $this->addSql("
            DROP INDEX IDX_5E7F4AB8460D9FD7 ON claro_resource_shortcut
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut CHANGE node_id resource_id INT NOT NULL
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
            DROP FOREIGN KEY FK_5D9559DC460D9FD7
        ");
        $this->addSql("
            DROP INDEX UNIQ_5D9559DC460D9FD7 ON claro_text
        ");
        $this->addSql("
            ALTER TABLE claro_text 
            DROP node_id, 
            CHANGE id id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_text 
            ADD CONSTRAINT FK_5D9559DCBF396750 FOREIGN KEY (id) 
            REFERENCES claro_resource (id) 
            ON DELETE CASCADE
        ");
    }
}