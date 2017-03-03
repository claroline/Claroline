<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2017/02/16 19:02:50
 */
class Version20170216190243 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE IF NOT EXISTS claro_icon_set (
                id INT AUTO_INCREMENT NOT NULL, 
                name VARCHAR(255) NOT NULL,
                cname VARCHAR(255) NOT NULL,
                is_default TINYINT(1) NOT NULL, 
                is_active TINYINT(1) NOT NULL,
                resource_stamp_icon VARCHAR(255) DEFAULT NULL,
                icon_sprite VARCHAR(255) DEFAULT NULL,
                icon_sprite_css VARCHAR(255) DEFAULT NULL,
                type VARCHAR(255) DEFAULT NULL, 
                UNIQUE INDEX UNIQ_D91D0EE67D75B9A (cname),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE IF NOT EXISTS claro_icon_item (
                id INT AUTO_INCREMENT NOT NULL, 
                icon_set_id INT DEFAULT NULL, 
                name VARCHAR(255) DEFAULT NULL, 
                class VARCHAR(255) DEFAULT NULL, 
                mime_type VARCHAR(255) DEFAULT NULL,
                relative_url VARCHAR(255) NOT NULL,
                resource_icon_id INT DEFAULT NULL,
                INDEX IDX_D727F16B91930DA (resource_icon_id),
                INDEX IDX_D727F16B48D16F3B (icon_set_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_icon_item 
            ADD CONSTRAINT FK_D727F16B48D16F3B FOREIGN KEY (icon_set_id) 
            REFERENCES claro_icon_set (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_icon_item
            ADD CONSTRAINT FK_D727F16B91930DA FOREIGN KEY (resource_icon_id)
            REFERENCES claro_resource_icon (id)
            ON DELETE CASCADE
        ');
        $this->addSql("
            INSERT IGNORE INTO claro_icon_set (`id`, `name`, `cname`, `is_default`, `is_active`, `type`)
            VALUES (1, 'Claroline', 'claroline', TRUE, TRUE, 'resource_icon_set')
        ");
        $this->addSql("
            INSERT INTO claro_icon_item (`icon_set_id`, `mime_type`, `relative_url`, `resource_icon_id`)
            SELECT 1, `mimeType`, `relative_url`, `id` FROM claro_resource_icon WHERE
            mimeType <> 'custom' AND mimeType IS NOT NULL AND is_shortcut = FALSE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            UPDATE claro_resource_icon ri, claro_icon_item ii
            SET ri.relative_url = ii.relative_url
            WHERE ri.id = ii.resource_icon_id
            AND ii.icon_set_id = 1
        ');
        $this->addSql('
            ALTER TABLE claro_icon_item 
            DROP FOREIGN KEY FK_D727F16B48D16F3B
        ');
        $this->addSql('
            ALTER TABLE claro_icon_item
            DROP FOREIGN KEY FK_D727F16B91930DA
        ');
        $this->addSql('
            DROP TABLE claro_icon_set
        ');
        $this->addSql('
            DROP TABLE claro_icon_item
        ');
    }
}
