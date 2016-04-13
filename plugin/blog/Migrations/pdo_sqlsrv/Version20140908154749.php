<?php

namespace Icap\BlogBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2014/09/08 03:47:50
 */
class Version20140908154749 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__blog_post ALTER COLUMN modification_date DATETIME2(6)
        ');
        $this->addSql('
            ALTER TABLE icap__blog_post ALTER COLUMN viewCounter INT NOT NULL
        ');
        $this->addSql('
            ALTER TABLE icap__blog_post 
            ADD CONSTRAINT DF_1B067922_7370281E DEFAULT 0 FOR viewCounter
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__blog_post ALTER COLUMN modification_date DATETIME2(6) NOT NULL
        ');
        $this->addSql('
            ALTER TABLE icap__blog_post 
            DROP CONSTRAINT DF_1B067922_7370281E
        ');
        $this->addSql('
            ALTER TABLE icap__blog_post ALTER COLUMN viewCounter INT NOT NULL
        ');
    }
}
