<?php

namespace UJM\ExoBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/07/17 10:31:29
 */
class Version20150717103127 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE ujm_label 
            ADD feedback LONGTEXT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE ujm_coords 
            ADD feedback LONGTEXT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE ujm_word_response 
            ADD feedback LONGTEXT DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE ujm_coords 
            DROP feedback
        ");
        $this->addSql("
            ALTER TABLE ujm_label 
            DROP feedback
        ");
        $this->addSql("
            ALTER TABLE ujm_word_response 
            DROP feedback
        ");
    }
}