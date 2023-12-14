<?php

namespace Claroline\HomeBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/11/22 07:35:09
 */
final class Version20231122073452 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_home_tab 
            DROP FOREIGN KEY FK_A9744CCE82D40A1F
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab 
            DROP FOREIGN KEY FK_A9744CCEA76ED395
        ');
        $this->addSql('
            DROP INDEX IDX_A9744CCE82D40A1F ON claro_home_tab
        ');
        $this->addSql('
            DROP INDEX IDX_A9744CCEA76ED395 ON claro_home_tab
        ');

        $this->addSql('
            DELETE FROM claro_home_tab WHERE user_id IS NOT NULL
        ');

        $this->addSql('
            ALTER TABLE claro_home_tab 
            ADD context_id VARCHAR(255) DEFAULT NULL,  
            CHANGE context context_name VARCHAR(255) NOT NULL
        ');

        $this->addSql('
            UPDATE claro_home_tab SET context_name = "desktop" WHERE context_name = "administration"
        ');
        $this->addSql('
            UPDATE claro_home_tab SET context_name = "administration" WHERE context_name = "admin"
        ');
        $this->addSql('
            UPDATE claro_home_tab SET context_name = "public" WHERE context_name = "home"
        ');

        $this->addSql('
            UPDATE claro_home_tab AS t
            LEFT JOIN claro_workspace AS w on (t.workspace_id = w.id)
            SET t.context_id = w.uuid 
            WHERE context_name = "workspace"
        ');

        $this->addSql('
            ALTER TABLE claro_home_tab  
            DROP user_id, 
            DROP workspace_id
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_home_tab 
            ADD user_id INT DEFAULT NULL, 
            ADD workspace_id INT DEFAULT NULL, 
            DROP context_id, 
            CHANGE context_name context VARCHAR(255) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab 
            ADD CONSTRAINT FK_A9744CCE82D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) ON UPDATE NO ACTION 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab 
            ADD CONSTRAINT FK_A9744CCEA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) ON UPDATE NO ACTION 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE INDEX IDX_A9744CCE82D40A1F ON claro_home_tab (workspace_id)
        ');
        $this->addSql('
            CREATE INDEX IDX_A9744CCEA76ED395 ON claro_home_tab (user_id)
        ');
    }
}
