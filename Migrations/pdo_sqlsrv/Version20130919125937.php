<?php

namespace Innova\PathBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/19 12:59:37
 */
class Version20130919125937 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE StepType (
                id INT IDENTITY NOT NULL, 
                name NVARCHAR(255) NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE TABLE StepWhere (
                id INT IDENTITY NOT NULL, 
                name NVARCHAR(255) NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE TABLE StepWho (
                id INT IDENTITY NOT NULL, 
                name NVARCHAR(255) NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE StepType
        ");
        $this->addSql("
            DROP TABLE StepWhere
        ");
        $this->addSql("
            DROP TABLE StepWho
        ");
    }
}