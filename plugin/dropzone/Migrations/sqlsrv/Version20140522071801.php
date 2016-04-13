<?php

namespace Icap\DropzoneBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2014/05/22 07:18:07
 */
class Version20140522071801 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__dropzonebundle_drop 
            ADD unlocked_drop BIT NOT NULL
        ');
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_drop 
            ADD CONSTRAINT DF_3AD19BA6_5A78C0A7 DEFAULT '0' FOR unlocked_drop
        ");
        $this->addSql('
            ALTER TABLE icap__dropzonebundle_drop 
            ADD unlocked_user BIT NOT NULL
        ');
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_drop 
            ADD CONSTRAINT DF_3AD19BA6_A7FE13CC DEFAULT '0' FOR unlocked_user
        ");
        $this->addSql('
            ALTER TABLE icap__dropzonebundle_dropzone 
            DROP COLUMN notify_on_drop
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__dropzonebundle_drop 
            DROP COLUMN unlocked_drop
        ');
        $this->addSql('
            ALTER TABLE icap__dropzonebundle_drop 
            DROP COLUMN unlocked_user
        ');
        $this->addSql('
            ALTER TABLE icap__dropzonebundle_dropzone 
            ADD notify_on_drop BIT NOT NULL
        ');
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            ADD CONSTRAINT DF_6782FC23_1B468EE2 DEFAULT '0' FOR notify_on_drop
        ");
    }
}
