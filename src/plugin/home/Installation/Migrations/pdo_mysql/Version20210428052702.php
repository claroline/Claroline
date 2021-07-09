<?php

namespace Claroline\HomeBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/04/28 05:27:03
 */
class Version20210428052702 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_home_tab 
            ADD name VARCHAR(255) DEFAULT NULL, 
            ADD longTitle LONGTEXT NOT NULL, 
            ADD centerTitle TINYINT(1) NOT NULL, 
            ADD showTitle TINYINT(1) DEFAULT "1" NOT NULL, 
            ADD icon VARCHAR(255) DEFAULT NULL, 
            ADD color VARCHAR(255) DEFAULT NULL, 
            ADD is_visible TINYINT(1) NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab_roles 
            DROP FOREIGN KEY FK_B81359F339727CCF
        ');
        $this->addSql('
            DROP INDEX IDX_B81359F339727CCF ON claro_home_tab_roles
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab_roles 
            DROP PRIMARY KEY
        ');

        // move config to home tab
        $this->addSql('
            UPDATE claro_home_tab AS t, (
                SELECT c1.*
                FROM claro_home_tab_config AS c1
                JOIN claro_home_tab AS t1 ON (t1.id = c1.home_tab_id)
                GROUP BY c1.home_tab_id
                ORDER BY c1.id ASC
            ) AS c
            SET 
                t.name = c.name, 
                t.longTitle = c.longTitle, 
                t.centerTitle = c.centerTitle, 
                t.showTitle = c.showTitle, 
                t.icon = c.icon, 
                t.color = c.color, 
                t.is_visible = c.is_visible
            WHERE t.id = c.home_tab_id
        ');

        // links roles to home tabs
        $this->addSql('
            UPDATE claro_home_tab_roles AS r
            LEFT JOIN claro_home_tab_config AS c ON (r.hometabconfig_id = c.id)
            LEFT JOIN claro_home_tab AS h ON (c.home_tab_id = h.id)
            SET r.hometabconfig_id = h.id
        ');

        $this->addSql('
            ALTER TABLE claro_home_tab_roles CHANGE hometabconfig_id hometab_id INT NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab_roles 
            ADD CONSTRAINT FK_B81359F3CCE862F FOREIGN KEY (hometab_id) 
            REFERENCES claro_home_tab (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE INDEX IDX_B81359F3CCE862F ON claro_home_tab_roles (hometab_id)
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab_roles 
            ADD PRIMARY KEY (hometab_id, role_id)
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_home_tab 
            DROP name, 
            DROP longTitle, 
            DROP centerTitle, 
            DROP showTitle, 
            DROP icon, 
            DROP color, 
            DROP is_visible
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab_roles 
            DROP FOREIGN KEY FK_B81359F3CCE862F
        ');
        $this->addSql('
            DROP INDEX IDX_B81359F3CCE862F ON claro_home_tab_roles
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab_roles 
            DROP PRIMARY KEY
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab_roles CHANGE hometab_id hometabconfig_id INT NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab_roles 
            ADD CONSTRAINT FK_B81359F339727CCF FOREIGN KEY (hometabconfig_id) 
            REFERENCES claro_home_tab_config (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE INDEX IDX_B81359F339727CCF ON claro_home_tab_roles (hometabconfig_id)
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab_roles 
            ADD PRIMARY KEY (hometabconfig_id, role_id)
        ');
    }
}
