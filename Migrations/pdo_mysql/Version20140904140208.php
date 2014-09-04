<?php

namespace Icap\DropzoneBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/09/04 02:02:12
 */
class Version20140904140208 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            DROP INDEX IDX_6782FC23E6B974D2, 
            ADD UNIQUE INDEX UNIQ_6782FC23E6B974D2 (event_agenda_drop)
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            DROP INDEX IDX_6782FC238D9E1321, 
            ADD UNIQUE INDEX UNIQ_6782FC238D9E1321 (event_agenda_correction)
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            ADD force_comment_in_correction TINYINT(1) NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            DROP INDEX UNIQ_6782FC23E6B974D2, 
            ADD INDEX IDX_6782FC23E6B974D2 (event_agenda_drop)
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            DROP INDEX UNIQ_6782FC238D9E1321, 
            ADD INDEX IDX_6782FC238D9E1321 (event_agenda_correction)
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            DROP force_comment_in_correction
        ");
    }
}