<?php

namespace Claroline\TagBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/11/08 09:50:47
 */
class Version20181108095008 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_tagbundle_tag 
            ADD uuid VARCHAR(36) NOT NULL,
            ADD description LONGTEXT DEFAULT NULL,
            ADD color VARCHAR(255) DEFAULT NULL
        ');
        $this->addSql('
            UPDATE claro_tagbundle_tag SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_6E5EC9DD17F50A6 ON claro_tagbundle_tag (uuid)
        ');
        $this->addSql('
            CREATE UNIQUE INDEX `unique` ON claro_tagbundle_tag (tag_name, user_id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP INDEX UNIQ_6E5EC9DD17F50A6 ON claro_tagbundle_tag
        ');
        $this->addSql('
            DROP INDEX `unique` ON claro_tagbundle_tag
        ');
        $this->addSql('
            ALTER TABLE claro_tagbundle_tag 
            DROP uuid,
            DROP description,
            DROP color
        ');
        $this->addSql('
            DROP INDEX `unique` ON claro_tagbundle_tagged_object
        ');
    }
}
