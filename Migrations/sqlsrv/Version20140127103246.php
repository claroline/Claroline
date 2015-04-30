<?php

namespace UJM\ExoBundle\Migrations\sqlsrv;

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
            ADD selector BIT
        ");
        $this->addSql("
            ALTER TABLE ujm_hole ALTER COLUMN position INT
        ");
        $this->addSql("
            ALTER TABLE ujm_hole ALTER COLUMN orthography BIT
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE ujm_hole 
            DROP COLUMN selector
        ");
        $this->addSql("
            ALTER TABLE ujm_hole ALTER COLUMN position INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE ujm_hole ALTER COLUMN orthography BIT NOT NULL
        ");
    }
}