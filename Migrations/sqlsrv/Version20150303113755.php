<?php

namespace HeVinci\CompetencyBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/03/03 11:37:56
 */
class Version20150303113755 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_11E77B9D5E237E06 ON hevinci_ability (name) 
            WHERE name IS NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'UNIQ_11E77B9D5E237E06'
            ) 
            ALTER TABLE hevinci_ability 
            DROP CONSTRAINT UNIQ_11E77B9D5E237E06 ELSE 
            DROP INDEX UNIQ_11E77B9D5E237E06 ON hevinci_ability
        ");
    }
}