<?php

namespace Claroline\OpenBadgeBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/01/27 07:10:49
 */
class Version20230127191036 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro__open_badge_rule 
            DROP FOREIGN KEY FK_DE554AC7460D9FD7
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_rule 
            DROP FOREIGN KEY FK_DE554AC782D40A1F
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_rule 
            DROP FOREIGN KEY FK_DE554AC7D60322AC
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_rule 
            DROP FOREIGN KEY FK_DE554AC7FE54D947
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_rule 
            ADD CONSTRAINT FK_DE554AC7460D9FD7 FOREIGN KEY (node_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_rule 
            ADD CONSTRAINT FK_DE554AC782D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_rule 
            ADD CONSTRAINT FK_DE554AC7D60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_rule 
            ADD CONSTRAINT FK_DE554AC7FE54D947 FOREIGN KEY (group_id) 
            REFERENCES claro_group (id) 
            ON DELETE SET NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro__open_badge_rule 
            DROP FOREIGN KEY FK_DE554AC7460D9FD7
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_rule 
            DROP FOREIGN KEY FK_DE554AC782D40A1F
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_rule 
            DROP FOREIGN KEY FK_DE554AC7D60322AC
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_rule 
            DROP FOREIGN KEY FK_DE554AC7FE54D947
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_rule 
            ADD CONSTRAINT FK_DE554AC7460D9FD7 FOREIGN KEY (node_id) 
            REFERENCES claro_resource_node (id)
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_rule 
            ADD CONSTRAINT FK_DE554AC782D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id)
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_rule 
            ADD CONSTRAINT FK_DE554AC7D60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id)
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_rule 
            ADD CONSTRAINT FK_DE554AC7FE54D947 FOREIGN KEY (group_id) 
            REFERENCES claro_group (id)
        ');
    }
}
