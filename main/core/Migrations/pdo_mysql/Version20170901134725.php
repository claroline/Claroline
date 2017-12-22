<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2017/09/01 01:47:27
 */
class Version20170901134725 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE INDEX value ON claro_resource_mask_decoder (value)
        ');
        $this->addSql('
            CREATE INDEX name ON claro_resource_mask_decoder (name)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP INDEX value ON claro_resource_mask_decoder
        ');
        $this->addSql('
            DROP INDEX name ON claro_resource_mask_decoder
        ');
    }
}
