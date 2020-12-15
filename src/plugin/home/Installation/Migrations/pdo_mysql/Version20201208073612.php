<?php

namespace Claroline\HomeBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/12/08 07:36:25
 */
class Version20201208073612 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_home_tab 
            ADD parent_id INT DEFAULT NULL, 
            ADD entity_order INT NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab 
            ADD CONSTRAINT FK_A9744CCE727ACA70 FOREIGN KEY (parent_id) 
            REFERENCES claro_home_tab (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE INDEX IDX_A9744CCE727ACA70 ON claro_home_tab (parent_id)
        ');

        $this->addSql('
            UPDATE claro_home_tab as t 
            LEFT JOIN claro_home_tab_config AS c ON (t.id = c.home_tab_id)
            SET t.entity_order = c.tab_order
        ');

        $this->addSql('
            ALTER TABLE claro_home_tab_config 
            DROP tab_order
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_home_tab 
            DROP FOREIGN KEY FK_A9744CCE727ACA70
        ');
        $this->addSql('
            DROP INDEX IDX_A9744CCE727ACA70 ON claro_home_tab
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab 
            DROP parent_id, 
            DROP entity_order
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab_config 
            ADD tab_order INT NOT NULL
        ');
    }
}
