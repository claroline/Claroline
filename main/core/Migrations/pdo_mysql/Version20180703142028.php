<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/07/03 02:20:30
 */
class Version20180703142028 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_widget_container
            ADD layout LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)'
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_widget_container
            DROP layout
        ');
    }
}
