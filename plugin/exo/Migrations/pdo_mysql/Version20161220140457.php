<?php

namespace UJM\ExoBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2016/12/20 02:05:00
 */
class Version20161220140457 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_question 
            DROP supplementary, 
            DROP specification
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_question 
            ADD supplementary LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci, 
            ADD specification LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci
        ');
    }
}
