<?php

namespace HeVinci\CompetencyBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/02/12 11:36:15
 */
class Version20150212113613 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_D3477F405E237E06 ON hevinci_scale (name) 
            WHERE name IS NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'UNIQ_D3477F405E237E06'
            ) 
            ALTER TABLE hevinci_scale 
            DROP CONSTRAINT UNIQ_D3477F405E237E06 ELSE 
            DROP INDEX UNIQ_D3477F405E237E06 ON hevinci_scale
        ");
    }
}