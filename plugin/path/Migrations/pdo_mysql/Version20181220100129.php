<?php

namespace Innova\PathBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/12/20 10:01:48
 */
class Version20181220100129 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE innova_path 
            DROP breadcrumbs, 
            DROP is_complete_blocking_condition
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE innova_path 
            ADD breadcrumbs TINYINT(1) NOT NULL, 
            ADD is_complete_blocking_condition TINYINT(1) NOT NULL
        ');
    }
}
