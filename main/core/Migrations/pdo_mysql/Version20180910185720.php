<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/09/10 06:57:21
 */
class Version20180910185720 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_widget_instance
            DROP FOREIGN KEY FK_5F89A385BC21F742
        ');
        $this->addSql('
            ALTER TABLE claro_widget_instance
            ADD CONSTRAINT FK_5F89A385BC21F742 FOREIGN KEY (container_id)
            REFERENCES claro_widget_container (id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_widget_instance
            DROP FOREIGN KEY FK_5F89A385BC21F742
        ');
        $this->addSql('
            ALTER TABLE claro_widget_instance
            ADD CONSTRAINT FK_5F89A385BC21F742 FOREIGN KEY (container_id)
            REFERENCES claro_widget_container (id)
            ON DELETE CASCADE
        ');
    }
}
