<?php

namespace Innova\PathBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2019/05/16 09:00:05
 */
class Version20190516090000 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE innova_path 
            DROP open_summary, 
            DROP show_summary
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE innova_path 
            ADD open_summary TINYINT(1) DEFAULT "1" NOT NULL, 
            ADD show_summary TINYINT(1) DEFAULT "1" NOT NULL
        ');
    }
}
