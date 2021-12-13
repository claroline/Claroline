<?php

namespace Claroline\DropZoneBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/11/05 10:27:31
 */
class Version20211105102704 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_dropzonebundle_drop 
            DROP reported, 
            DROP drop_number
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_dropzonebundle_drop 
            ADD reported TINYINT(1) NOT NULL, 
            ADD drop_number INT DEFAULT NULL
        ');
    }
}
