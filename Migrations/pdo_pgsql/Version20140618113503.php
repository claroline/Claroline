<?php

namespace Claroline\CoreBundle\Migrations\pdo_pgsql;

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
            ADD who VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_activity_parameters 
            ADD \"where\" VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_activity_parameters 
            ADD with_tutor BOOLEAN DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_activity_parameters 
            DROP who
        ");
        $this->addSql("
            ALTER TABLE claro_activity_parameters 
            DROP \"where\"
        ");
        $this->addSql("
            ALTER TABLE claro_activity_parameters 
            DROP with_tutor
        ");
    }
}