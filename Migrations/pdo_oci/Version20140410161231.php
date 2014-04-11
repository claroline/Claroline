<?php

namespace UJM\ExoBundle\Migrations\pdo_oci;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/04/10 04:12:33
 */
class Version20140410161231 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE ujm_exercise 
            ADD (
                published NUMBER(1) NOT NULL
            )
        ");
        $this->addSql("
            UPDATE ujm_exercise SET published=1
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE ujm_exercise 
            DROP (published)
        ");
    }
}