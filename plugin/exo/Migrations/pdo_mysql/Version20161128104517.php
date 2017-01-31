<?php

namespace UJM\ExoBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2016/11/28 10:45:20
 */
class Version20161128104517 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // Add new columns
        $this->addSql('
            ALTER TABLE ujm_exercise 
            ADD random_order VARCHAR(255) NOT NULL, 
            ADD random_pick VARCHAR(255) NOT NULL,  
            CHANGE nb_question pick INT NOT NULL
        ');
        // Migrate data
        $this->addSql("
            UPDATE ujm_exercise SET random_pick='never' WHERE pick = 0
        ");
        $this->addSql("
            UPDATE ujm_exercise SET random_pick='once' WHERE pick > 0 AND keepSameQuestion = 1
        ");
        $this->addSql("
            UPDATE ujm_exercise SET random_pick='always' WHERE pick > 0 AND (keepSameQuestion=0 OR keepSameQuestion IS NULL)
        ");
        $this->addSql("
            UPDATE ujm_exercise SET random_order='never' WHERE shuffle = 0
        ");
        $this->addSql("
            UPDATE ujm_exercise SET random_order='once' WHERE shuffle = 1 AND keepSameQuestion = 1
        ");
        $this->addSql("
            UPDATE ujm_exercise SET random_order='always' WHERE shuffle = 1 AND (keepSameQuestion=0 OR keepSameQuestion IS NULL) 
        ");
        // Drop old columns
        $this->addSql('
            ALTER TABLE ujm_exercise 
            DROP shuffle, 
            DROP keepSameQuestion
        ');

        // Add new columns
        $this->addSql('
            ALTER TABLE ujm_step 
            ADD random_order VARCHAR(255) NOT NULL, 
            ADD random_pick VARCHAR(255) NOT NULL, 
            CHANGE nbquestion pick INT NOT NULL
        ');
        // Migrate data
        $this->addSql("
            UPDATE ujm_step SET random_pick='never' WHERE pick = 0
        ");
        $this->addSql("
            UPDATE ujm_step SET random_pick='once' WHERE pick > 0 AND keepSameQuestion = 1
        ");
        $this->addSql("
            UPDATE ujm_step SET random_pick='always' WHERE pick > 0 AND (keepSameQuestion=0 OR keepSameQuestion IS NULL)
        ");
        $this->addSql("
            UPDATE ujm_step SET random_order='never' WHERE shuffle = 0
        ");
        $this->addSql("
            UPDATE ujm_step SET random_order='once' WHERE shuffle = 1 AND keepSameQuestion = 1
        ");
        $this->addSql("
            UPDATE ujm_step SET random_order='always' WHERE shuffle = 1 AND (keepSameQuestion=0 OR keepSameQuestion IS NULL) 
        ");
        // Drop old columns
        $this->addSql('
            ALTER TABLE ujm_step 
            DROP shuffle, 
            DROP keepSameQuestion
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_exercise 
            ADD shuffle TINYINT(1) NOT NULL, 
            ADD keepSameQuestion TINYINT(1) DEFAULT NULL, 
            DROP random_order, 
            DROP random_pick, 
            CHANGE pick nb_question INT NOT NULL
        ');
        $this->addSql('
            ALTER TABLE ujm_step 
            ADD keepSameQuestion TINYINT(1) DEFAULT NULL, 
            ADD shuffle TINYINT(1) NOT NULL, 
            DROP random_order, 
            DROP random_pick, 
            CHANGE pick nbQuestion INT NOT NULL
        ');
    }
}
