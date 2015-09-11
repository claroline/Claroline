<?php

namespace UJM\ExoBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/07/07 04:42:01
 */
class Version20150707164200 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_exercise_player 
            DROP published, 
            DROP modified, 
            DROP creation, 
            DROP modification
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_exercise_player 
            ADD published TINYINT(1) NOT NULL, 
            ADD modified TINYINT(1) NOT NULL, 
            ADD creation DATETIME NOT NULL, 
            ADD modification DATETIME NOT NULL
        ');
    }
}
