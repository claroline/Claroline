<?php

namespace UJM\ExoBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/06/03 07:33:05
 */
class Version20210603073254 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE ujm_exercise 
            ADD endStats VARCHAR(255) NOT NULL, 
            ADD overviewStats VARCHAR(255) NOT NULL
        ');

        $this->addSql('
            UPDATE ujm_exercise SET endStats = "none", overviewStats = "none"
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE ujm_exercise 
            DROP endStats, 
            DROP overviewStats
        ');
    }
}
