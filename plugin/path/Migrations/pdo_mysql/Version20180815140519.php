<?php

namespace Innova\PathBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/08/15 02:05:21
 */
class Version20180815140519 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE innova_path 
            CHANGE summary_displayed open_summary TINYINT(1) DEFAULT "1" NOT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE innova_path 
            CHANGE open_summary summary_displayed TINYINT(1) DEFAULT "1" NOT NULL
        ');
    }
}
