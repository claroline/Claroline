<?php

namespace Claroline\CoreBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/09/20 08:16:13
 */
class Version20210920081609 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_field_facet 
            DROP FOREIGN KEY FK_F6C21DB28A5F48FF
        ');
        $this->addSql('
            DROP INDEX IDX_F6C21DB28A5F48FF ON claro_field_facet
        ');
        $this->addSql('
            ALTER TABLE claro_field_facet 
            DROP resource_node
        ');
        $this->addSql('
            ALTER TABLE claro_field_facet CHANGE options options LONGTEXT NOT NULL COMMENT "(DC2Type:json)"
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_field_facet 
            ADD resource_node INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_field_facet 
            ADD CONSTRAINT FK_F6C21DB28A5F48FF FOREIGN KEY (resource_node) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE INDEX IDX_F6C21DB28A5F48FF ON claro_field_facet (resource_node)
        ');
        $this->addSql('
            ALTER TABLE claro_field_facet CHANGE options options LONGTEXT CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci` COMMENT "(DC2Type:json_array)"
        ');
    }
}
