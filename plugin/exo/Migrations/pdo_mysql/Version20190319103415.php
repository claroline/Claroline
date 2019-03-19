<?php

namespace UJM\ExoBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2019/03/19 10:34:49
 */
class Version20190319103415 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_interaction_qcm 
            ADD `direction` VARCHAR(255) NOT NULL
        ');

        $this->addSql('
            UPDATE ujm_interaction_qcm SET `direction` = "vertical"
        ');

        // update MarkMode
        $this->addSql('
            UPDATE ujm_exercise SET mark_mode = "correction" WHERE mark_mode = "1"
        ');
        $this->addSql('
            UPDATE ujm_exercise SET mark_mode = "validation" WHERE mark_mode = "2"
        ');
        $this->addSql('
            UPDATE ujm_exercise SET mark_mode = "never" WHERE mark_mode = "3"
        ');

        // update CorrectionMode
        $this->addSql('
            UPDATE ujm_exercise SET correction_mode = "validation" WHERE correction_mode = "1"
        ');
        $this->addSql('
            UPDATE ujm_exercise SET correction_mode = "lastAttempt" WHERE correction_mode = "2"
        ');
        $this->addSql('
            UPDATE ujm_exercise SET correction_mode = "date" WHERE correction_mode = "3"
        ');
        $this->addSql('
            UPDATE ujm_exercise SET correction_mode = "never" WHERE correction_mode = "4"
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_interaction_qcm 
            DROP `direction`
        ');
    }
}
