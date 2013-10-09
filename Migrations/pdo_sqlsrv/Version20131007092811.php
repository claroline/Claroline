<?php

namespace Innova\PathBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/10/07 09:28:12
 */
class Version20131007092811 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_pathtemplate 
            DROP COLUMN [user]
        ");
        $this->addSql("
            ALTER TABLE innova_pathtemplate 
            DROP COLUMN edit_date
        ");
        $this->addSql("
            ALTER TABLE innova_pathtemplate ALTER COLUMN description VARCHAR(MAX)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_pathtemplate 
            ADD [user] NVARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE innova_pathtemplate 
            ADD edit_date DATETIME2(6) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE innova_pathtemplate ALTER COLUMN description VARCHAR(MAX) NOT NULL
        ");
    }
}