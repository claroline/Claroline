<?php

namespace UJM\ExoBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2024/06/18 05:31:11
 */
final class Version20240618053110 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE ujm_exercise 
            DROP max_papers, 
            DROP max_day_attempts
        ');
        $this->addSql('
            ALTER TABLE ujm_step 
            DROP max_day_attempts
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE ujm_exercise 
            ADD max_papers INT NOT NULL, 
            ADD max_day_attempts INT NOT NULL
        ');
        $this->addSql('
            ALTER TABLE ujm_step 
            ADD max_day_attempts INT NOT NULL
        ');
    }
}
