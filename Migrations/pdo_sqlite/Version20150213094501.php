<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/02/13 09:45:03
 */
class Version20150213094501 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_profile_property (
                id INTEGER NOT NULL, 
                role_id INTEGER DEFAULT NULL, 
                is_editable BOOLEAN NOT NULL, 
                property VARCHAR(256) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_C2B93182D60322AC ON claro_profile_property (role_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_profile_property
        ");
    }
}