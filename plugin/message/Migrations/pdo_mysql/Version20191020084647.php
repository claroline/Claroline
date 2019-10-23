<?php

namespace Claroline\MessageBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2019/10/20 08:46:49
 */
class Version20191020084647 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_message 
            DROP is_removed
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_message 
            ADD is_removed TINYINT(1) NOT NULL
        ');
    }
}
