<?php

namespace UJM\ExoBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2017/01/13 12:24:21
 */
class Version20170113122419 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE ujm_response 
            ADD used_hints LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)'
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_response 
            DROP used_hints
        ');
    }
}
