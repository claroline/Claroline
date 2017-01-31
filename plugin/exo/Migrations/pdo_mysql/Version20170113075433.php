<?php

namespace UJM\ExoBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2017/01/13 07:54:35
 */
class Version20170113075433 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_hint 
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE ujm_hint SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_B5FFCBE7D17F50A6 ON ujm_hint (uuid)
        ');

        $this->addSql('
            ALTER TABLE ujm_coords 
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE ujm_coords SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_CD7B4982D17F50A6 ON ujm_coords (uuid)
        ');

        $this->addSql('
            ALTER TABLE ujm_choice 
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE ujm_choice SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_D4BDFA95D17F50A6 ON ujm_choice (uuid)
        ');

        $this->addSql('
            ALTER TABLE ujm_hole 
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE ujm_hole SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_E9F4F525D17F50A6 ON ujm_hole (uuid)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP INDEX UNIQ_D4BDFA95D17F50A6 ON ujm_choice
        ');
        $this->addSql('
            ALTER TABLE ujm_choice 
            DROP uuid
        ');
        $this->addSql('
            DROP INDEX UNIQ_CD7B4982D17F50A6 ON ujm_coords
        ');
        $this->addSql('
            ALTER TABLE ujm_coords 
            DROP uuid
        ');
        $this->addSql('
            DROP INDEX UNIQ_B5FFCBE7D17F50A6 ON ujm_hint
        ');
        $this->addSql('
            ALTER TABLE ujm_hint 
            DROP uuid
        ');
        $this->addSql('
            DROP INDEX UNIQ_E9F4F525D17F50A6 ON ujm_hole
        ');
        $this->addSql('
            ALTER TABLE ujm_hole 
            DROP uuid
        ');
    }
}
