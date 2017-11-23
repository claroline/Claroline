<?php

namespace UJM\ExoBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2017/11/22 08:33:14
 */
class Version20171122083312 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_exercise 
            DROP random_tag, 
            CHANGE pick pick LONGTEXT NOT NULL
        ');
        $this->addSql('
            ALTER TABLE ujm_step 
            ADD picking VARCHAR(255) NOT NULL, 
            DROP random_tag
        ');
        $this->addSql("
            UPDATE ujm_step 
            SET picking = 'standard'
            WHERE picking IS NULL OR picking = ''
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE ujm_exercise 
            ADD random_tag LONGTEXT NOT NULL COLLATE utf8_unicode_ci COMMENT '(DC2Type:array)', 
            CHANGE pick pick INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE ujm_step 
            ADD random_tag LONGTEXT NOT NULL COLLATE utf8_unicode_ci COMMENT '(DC2Type:array)', 
            DROP picking
        ");
    }
}
