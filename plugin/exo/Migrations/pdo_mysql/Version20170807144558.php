<?php

namespace UJM\ExoBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2017/08/07 02:45:59
 */
class Version20170807144558 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_step 
            ADD max_day_attempts INT NOT NULL
        ');
        $this->addSql('
            ALTER TABLE ujm_exercise 
            ADD max_day_attempts INT NOT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_exercise 
            DROP max_day_attempts
        ');
        $this->addSql('
            ALTER TABLE ujm_step 
            DROP max_day_attempts
        ');
    }
}
