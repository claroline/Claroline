<?php

namespace Claroline\HomeBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/11/17 06:44:14
 */
class Version20201117064352 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_home_tab ADD class VARCHAR(255) DEFAULT NULL
        ');
        $this->addSql('
            UPDATE claro_home_tab SET class = "Claroline\\\HomeBundle\\\Entity\\\Type\\\WidgetsTab"
        ');

        $this->addSql('
            CREATE TABLE claro_home_tab_widgets (
                id INT AUTO_INCREMENT NOT NULL, 
                tab_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_E813FD848D0C9323 (tab_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            INSERT INTO claro_home_tab_widgets (id, tab_id)
                SELECT id, id as tab_id
                FROM claro_home_tab
        ');

        $this->addSql('
            CREATE TABLE claro_home_tab_widgets_containers (
                tab_id INT NOT NULL, 
                container_id INT NOT NULL, 
                INDEX IDX_151555278D0C9323 (tab_id), 
                UNIQUE INDEX UNIQ_15155527BC21F742 (container_id), 
                PRIMARY KEY(tab_id, container_id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            INSERT INTO claro_home_tab_widgets_containers (container_id, tab_id)
                SELECT id as container_id, hometab_id as tab_id
                FROM claro_widget_container
                WHERE hometab_id IS NOT NULL
        ');

        $this->addSql('
            ALTER TABLE claro_widget_container DROP FOREIGN KEY FK_3B06DD75CCE862F
        ');

        $this->addSql('
            DROP INDEX IDX_3B06DD75CCE862F ON claro_widget_container
        ');

        $this->addSql('
            ALTER TABLE claro_widget_container DROP hometab_id
        ');

        $this->addSql('
            DELETE c FROM claro_home_tab_widgets_containers AS c LEFT JOIN claro_home_tab_widgets AS w ON c.tab_id = w.id WHERE w.id IS NULL
        ');

        $this->addSql('
            ALTER TABLE claro_home_tab_widgets 
            ADD CONSTRAINT FK_E813FD848D0C9323 FOREIGN KEY (tab_id) 
            REFERENCES claro_home_tab (id) ON DELETE CASCADE
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

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_home_tab_widgets_containers 
            DROP FOREIGN KEY FK_151555278D0C9323
        ');
        $this->addSql('
            DROP TABLE claro_home_tab_widgets
        ');
        $this->addSql('
            DROP TABLE claro_home_tab_widgets_containers
        ');
    }
}
