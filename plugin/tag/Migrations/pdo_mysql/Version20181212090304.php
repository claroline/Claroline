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
        // removes duplicated tagged objects
        $this->addSql('
            DELETE t1
            FROM claro_tagbundle_tagged_object AS t1
            INNER JOIN claro_tagbundle_tagged_object AS t2
            WHERE t1.id < t2.id
            AND t1.object_class = t2.object_class
            AND t1.object_id = t2.object_id
            AND t1.tag_id = t2.tag_id
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
