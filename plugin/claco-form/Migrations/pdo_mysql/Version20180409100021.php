<?php

namespace Claroline\ClacoFormBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/04/09 10:00:22
 */
class Version20180409100021 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_clacoformbundle_field 
            ADD field_order INT DEFAULT 1000 NOT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_clacoformbundle_field 
            DROP field_order
        ');
    }
}
