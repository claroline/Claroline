<?php

namespace Claroline\TagBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/12/12 09:03:07
 */
class Version20181212090304 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            DROP INDEX `unique` ON claro_tagbundle_tagged_object
        ');
        $this->addSql('
            CREATE UNIQUE INDEX `unique` ON claro_tagbundle_tagged_object (object_id, object_class, tag_id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP INDEX `unique` ON claro_tagbundle_tagged_object
        ');
        $this->addSql('
            CREATE UNIQUE INDEX `unique` ON claro_tagbundle_tagged_object (
                object_id, object_class, object_name
            )
        ');
    }
}
