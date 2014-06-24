<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/06/18 11:35:04
 */
class Version20140618113503 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_activity_parameters 
            ADD who NVARCHAR(255)
        ");
        $this->addSql("
            ALTER TABLE claro_activity_parameters 
            ADD [where] NVARCHAR(255)
        ");
        $this->addSql("
            ALTER TABLE claro_activity_parameters 
            ADD with_tutor BIT
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_activity_parameters 
            DROP COLUMN who
        ");
        $this->addSql("
            ALTER TABLE claro_activity_parameters 
            DROP COLUMN [where]
        ");
        $this->addSql("
            ALTER TABLE claro_activity_parameters 
            DROP COLUMN with_tutor
        ");
    }
}