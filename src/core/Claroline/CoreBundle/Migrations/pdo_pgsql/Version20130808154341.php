<?php

namespace Claroline\CoreBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/08/08 03:43:42
 */
class Version20130808154341 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE SEQUENCE claro_resource_shortcut_id_seq
        ");
        $this->addSql("
            SELECT setval(
                'claro_resource_shortcut_id_seq', 
                (
                    SELECT MAX(id) 
                    FROM claro_resource_shortcut
                )
            )
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut ALTER id 
            SET 
                DEFAULT nextval(
                    'claro_resource_shortcut_id_seq'
                )
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_resource_shortcut ALTER id 
            DROP DEFAULT
        ");
    }
}