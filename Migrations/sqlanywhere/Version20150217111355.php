<?php

namespace Claroline\CoreBundle\Migrations\sqlanywhere;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/02/17 11:13:59
 */
class Version20150217111355 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_bundle (
                id INT IDENTITY NOT NULL, 
                name VARCHAR(100) NOT NULL, 
                version VARCHAR(50) NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_bundle
        ");
    }
}