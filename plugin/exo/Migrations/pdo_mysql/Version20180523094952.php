<?php

namespace UJM\ExoBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/05/23 09:49:53
 */
class Version20180523094952 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_step_question 
            ADD mandatory TINYINT(1) DEFAULT NULL
        ');
        $this->addSql("
            ALTER TABLE ujm_exercise 
            ADD time_limited TINYINT(1) DEFAULT '0' NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_exercise 
            DROP time_limited
        ');
        $this->addSql('
            ALTER TABLE ujm_step_question 
            DROP mandatory
        ');
    }
}
