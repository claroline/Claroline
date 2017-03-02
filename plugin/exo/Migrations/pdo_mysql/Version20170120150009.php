<?php

namespace UJM\ExoBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2017/01/20 03:00:17
 */
class Version20170120150009 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_response 
            ADD uuid VARCHAR(36) NOT NULL
        ');
        // The new column needs to be filled to be able to add the UNIQUE constraint
        $this->addSql('
            UPDATE ujm_response SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_A7EC2BC2D17F50A6 ON ujm_response (uuid)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP INDEX UNIQ_A7EC2BC2D17F50A6 ON ujm_response
        ');
        $this->addSql('
            ALTER TABLE ujm_response 
            DROP uuid
        ');
    }
}
