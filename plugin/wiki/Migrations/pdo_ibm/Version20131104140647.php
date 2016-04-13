<?php

namespace Icap\WikiBundle\Migrations\pdo_ibm;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2013/11/04 02:06:47
 */
class Version20131104140647 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__wiki_section 
            ADD COLUMN deleted SMALLINT DEFAULT NULL 
            ADD COLUMN deletion_date TIMESTAMP(0) DEFAULT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__wiki_section 
            DROP COLUMN deleted 
            DROP COLUMN deletion_date
        ');
    }
}
