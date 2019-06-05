<?php

namespace Claroline\ForumBundle\Migrations\pdo_mysql;

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
        // fixes tags on forum subjects
        $this->addSql('
            UPDATE claro_tagbundle_tagged_object AS t
            LEFT JOIN claro_forum_subject AS f ON (TRIM(t.object_name) = TRIM(f.title))
            SET t.object_id = f.uuid
            WHERE t.object_id = "0" AND object_class = "Claroline\\ForumBundle\\Entity\\Subject"
        ');
    }

    public function down(Schema $schema)
    {
    }
}
