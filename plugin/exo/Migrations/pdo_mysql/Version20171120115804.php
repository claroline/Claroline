<?php

namespace UJM\ExoBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2017/11/20 11:58:16
 */
class Version20171120115804 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_exercise 
            ADD picking VARCHAR(255) NOT NULL
        ');
        $this->addSql("
            UPDATE ujm_exercise 
            SET picking = 'standard'
            WHERE picking IS NULL OR picking = ''
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_exercise 
            DROP picking
        ');
    }
}
