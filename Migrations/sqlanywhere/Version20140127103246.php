<?php

namespace UJM\ExoBundle\Migrations\sqlanywhere;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/01/27 10:32:48
 */
class Version20140127103246 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE ujm_hole 
            ADD selector BIT NULL DEFAULT NULL, 
            ALTER position INT DEFAULT NULL, 
            ALTER orthography BIT NULL DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE ujm_hole 
            DROP selector, 
            ALTER position INT NOT NULL, 
            ALTER orthography BIT NOT NULL
        ");
    }
}