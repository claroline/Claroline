<?php

namespace Claroline\AnnouncementBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/08/19 03:53:28
 */
class Version20130819155327 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_announcement ALTER COLUMN creation_date DATETIME2(6) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_announcement ALTER COLUMN publication_date DATETIME2(6)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_announcement ALTER COLUMN creation_date DATE NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_announcement ALTER COLUMN publication_date DATE
        ");
    }
}