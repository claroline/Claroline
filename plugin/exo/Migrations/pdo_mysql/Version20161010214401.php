<?php

namespace UJM\ExoBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2016/10/10 09:44:04
 *
 * @todo : to remove. do not touch it. It's already in exo-v2 branch
 */
class Version20161010214401 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_exercise 
            ADD uuid VARCHAR(36) NOT NULL
        ');

        // The new column needs to be filled to be able to add the UNIQUE constraint
        $this->addSql('
            UPDATE ujm_exercise SET uuid = (SELECT UUID())
        ');

        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_374DF525D17F50A6 ON ujm_exercise (uuid)
        ');

        $this->addSql('
            ALTER TABLE ujm_step 
            ADD uuid VARCHAR(36) NOT NULL
        ');

        // The new column needs to be filled to be able to add the UNIQUE constraint
        $this->addSql('
            UPDATE ujm_step SET uuid = (SELECT UUID())
        ');

        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_C2803688D17F50A6 ON ujm_step (uuid)
        ');

        $this->addSql('
            ALTER TABLE ujm_question 
            ADD uuid VARCHAR(36) NOT NULL
        ');

        // The new column needs to be filled to be able to add the UNIQUE constraint
        $this->addSql('
            UPDATE ujm_question SET uuid = (SELECT UUID())
        ');

        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_2F606977D17F50A6 ON ujm_question (uuid)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP INDEX UNIQ_374DF525D17F50A6 ON ujm_exercise
        ');

        $this->addSql('
            ALTER TABLE ujm_exercise
            DROP uuid
        ');

        $this->addSql('
            DROP INDEX UNIQ_C2803688D17F50A6 ON ujm_step
        ');

        $this->addSql('
            ALTER TABLE ujm_step 
            DROP uuid
        ');

        $this->addSql('
            DROP INDEX UNIQ_2F606977D17F50A6 ON ujm_question
        ');

        $this->addSql('
            ALTER TABLE ujm_question 
            DROP uuid
        ');
    }
}
