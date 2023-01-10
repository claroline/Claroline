<?php

namespace UJM\ExoBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/01/13 12:56:58
 */
class Version20230113125657 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE ujm_exercise 
            ADD end_back_type LONGTEXT DEFAULT NULL, 
            ADD end_back_title LONGTEXT DEFAULT NULL, 
            ADD end_back_target LONGTEXT DEFAULT NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE ujm_exercise 
            DROP end_back_type, 
            DROP end_back_title, 
            DROP end_back_target
        ');
    }
}
