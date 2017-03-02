<?php

namespace UJM\ExoBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2016/10/04 09:51:55
 */
class Version20161004215152 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_choice CHANGE `label` `label` LONGTEXT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE ujm_hole 
            ADD placeholder VARCHAR(255) DEFAULT NULL, 
            DROP orthography
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_choice CHANGE `label` `label` LONGTEXT NOT NULL COLLATE utf8_unicode_ci
        ');
        $this->addSql('
            ALTER TABLE ujm_hole 
            ADD orthography TINYINT(1) DEFAULT NULL, 
            DROP placeholder
        ');
    }
}
