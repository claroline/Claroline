<?php

namespace Icap\BibliographyBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/06/28 11:04:03
 */
class Version20180628110402 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__bibliography_book_reference 
            ADD uuid VARCHAR(36) NOT NULL, 
            CHANGE author author VARCHAR(255) DEFAULT NULL
        ');
        $this->addSql('
            UPDATE icap__bibliography_book_reference
            SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_D961F495D17F50A6 ON icap__bibliography_book_reference (uuid)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP INDEX UNIQ_D961F495D17F50A6 ON icap__bibliography_book_reference
        ');
        $this->addSql('
            ALTER TABLE icap__bibliography_book_reference 
            DROP uuid, 
            CHANGE author author VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci
        ');
    }
}
