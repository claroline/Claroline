<?php

namespace UJM\ExoBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2019/04/08 01:32:03
 */
class Version20190408133132 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_exercise 
            ADD success_message LONGTEXT DEFAULT NULL, 
            ADD failure_message LONGTEXT DEFAULT NULL, 
            DROP published
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_exercise 
            ADD published TINYINT(1) NOT NULL, 
            DROP success_message, 
            DROP failure_message
        ');
    }
}
