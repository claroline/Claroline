<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2019/05/16 08:59:38
 */
class Version20190516085924 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_directory 
            DROP show_summary, 
            DROP open_summary
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_directory 
            ADD show_summary TINYINT(1) DEFAULT "1" NOT NULL, 
            ADD open_summary TINYINT(1) DEFAULT "1" NOT NULL
        ');
    }
}
