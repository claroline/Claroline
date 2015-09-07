<?php

namespace UJM\ExoBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/09/07 02:34:19
 */
class Version20150907143418 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE ujm_exercise 
            DROP start_date, 
            DROP use_date_end, 
            DROP end_date
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE ujm_exercise 
            ADD start_date DATETIME NOT NULL, 
            ADD use_date_end TINYINT(1) DEFAULT NULL, 
            ADD end_date DATETIME DEFAULT NULL
        ");
    }
}