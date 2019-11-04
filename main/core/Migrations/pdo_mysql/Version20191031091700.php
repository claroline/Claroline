<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2019/10/31 09:17:02
 */
class Version20191031091700 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_resource_node 
            DROP FOREIGN KEY FK_A76799FF54B9D732
        ');
        $this->addSql('
            DROP INDEX IDX_A76799FF54B9D732 ON claro_resource_node
        ');
        $this->addSql('
            ALTER TABLE claro_resource_node 
            DROP icon_id
        ');
        $this->addSql('
            ALTER TABLE claro_icon_set 
            DROP icon_sprite, 
            DROP icon_sprite_css, 
            DROP resource_stamp_icon
        ');
        $this->addSql('
            ALTER TABLE claro_icon_item 
            DROP FOREIGN KEY FK_D727F16B91930DA
        ');
        $this->addSql('
            DROP INDEX IDX_D727F16B91930DA ON claro_icon_item
        ');
        $this->addSql('
            ALTER TABLE claro_icon_item 
            DROP resource_icon_id
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_icon_item 
            ADD resource_icon_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_icon_item 
            ADD CONSTRAINT FK_D727F16B91930DA FOREIGN KEY (resource_icon_id) 
            REFERENCES claro_resource_icon (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE INDEX IDX_D727F16B91930DA ON claro_icon_item (resource_icon_id)
        ');
        $this->addSql('
            ALTER TABLE claro_icon_set 
            ADD icon_sprite VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, 
            ADD icon_sprite_css VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, 
            ADD resource_stamp_icon VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci
        ');
        $this->addSql('
            ALTER TABLE claro_resource_node 
            ADD icon_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_resource_node 
            ADD CONSTRAINT FK_A76799FF54B9D732 FOREIGN KEY (icon_id) 
            REFERENCES claro_resource_icon (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            CREATE INDEX IDX_A76799FF54B9D732 ON claro_resource_node (icon_id)
        ');
    }
}
