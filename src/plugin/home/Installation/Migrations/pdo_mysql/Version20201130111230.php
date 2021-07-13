<?php

namespace Claroline\HomeBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/11/30 11:12:50
 */
class Version20201130111230 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_home_tab_widgets_containers 
            DROP FOREIGN KEY FK_151555278D0C9323
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab_widgets_containers 
            DROP FOREIGN KEY FK_15155527BC21F742
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab_widgets_containers 
            ADD CONSTRAINT FK_151555278D0C9323 FOREIGN KEY (tab_id) 
            REFERENCES claro_home_tab_widgets (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab_widgets_containers 
            ADD CONSTRAINT FK_15155527BC21F742 FOREIGN KEY (container_id) 
            REFERENCES claro_widget_container (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_home_tab_widgets_containers 
            DROP FOREIGN KEY FK_151555278D0C9323
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab_widgets_containers 
            DROP FOREIGN KEY FK_15155527BC21F742
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab_widgets_containers 
            ADD CONSTRAINT FK_151555278D0C9323 FOREIGN KEY (tab_id) 
            REFERENCES claro_home_tab_widgets (id)
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab_widgets_containers 
            ADD CONSTRAINT FK_15155527BC21F742 FOREIGN KEY (container_id) 
            REFERENCES claro_widget_container (id)
        ');
    }
}
