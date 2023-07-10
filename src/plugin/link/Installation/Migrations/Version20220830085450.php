<?php

namespace Claroline\LinkBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/07/10 02:42:29
 */
final class Version20220830085450 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE claro_home_tab_tool_shortcut (
                id INT AUTO_INCREMENT NOT NULL, 
                tab_id INT DEFAULT NULL, 
                tool VARCHAR(255) NOT NULL, 
                UNIQUE INDEX UNIQ_ED7350108D0C9323 (tab_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_resource_shortcut (
                id INT AUTO_INCREMENT NOT NULL, 
                target_id INT DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_5E7F4AB8D17F50A6 (uuid), 
                INDEX IDX_5E7F4AB8158E0B66 (target_id), 
                UNIQUE INDEX UNIQ_5E7F4AB8B87FAB32 (resourceNode_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab_tool_shortcut 
            ADD CONSTRAINT FK_ED7350108D0C9323 FOREIGN KEY (tab_id) 
            REFERENCES claro_home_tab (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_resource_shortcut 
            ADD CONSTRAINT FK_5E7F4AB8158E0B66 FOREIGN KEY (target_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_resource_shortcut 
            ADD CONSTRAINT FK_5E7F4AB8B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_home_tab_tool_shortcut 
            DROP FOREIGN KEY FK_ED7350108D0C9323
        ');
        $this->addSql('
            ALTER TABLE claro_resource_shortcut 
            DROP FOREIGN KEY FK_5E7F4AB8158E0B66
        ');
        $this->addSql('
            ALTER TABLE claro_resource_shortcut 
            DROP FOREIGN KEY FK_5E7F4AB8B87FAB32
        ');
        $this->addSql('
            DROP TABLE claro_home_tab_tool_shortcut
        ');
        $this->addSql('
            DROP TABLE claro_resource_shortcut
        ');
    }
}
