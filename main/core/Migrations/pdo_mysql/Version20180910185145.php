<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/09/10 06:51:46
 */
class Version20180910185145 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_widget_container
            DROP FOREIGN KEY FK_3B06DD75B628319
        ');
        $this->addSql('
            ALTER TABLE claro_widget_container
            ADD CONSTRAINT FK_3B06DD75B628319 FOREIGN KEY (homeTab_id)
            REFERENCES claro_home_tab (id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_widget_container
            DROP FOREIGN KEY FK_3B06DD75B628319
        ');
        $this->addSql('
            ALTER TABLE claro_widget_container
            ADD CONSTRAINT FK_3B06DD75B628319 FOREIGN KEY (homeTab_id)
            REFERENCES claro_home_tab (id)
            ON DELETE CASCADE
        ');
    }
}
