<?php

namespace Claroline\DropZoneBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2024/05/27 07:27:27
 */
final class Version20240527072623 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_dropzonebundle_dropzone 
            DROP edition_state, 
            DROP auto_close_state, 
            DROP notify_on_drop
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_dropzonebundle_dropzone 
            ADD edition_state SMALLINT NOT NULL, 
            ADD auto_close_state INT NOT NULL, 
            ADD notify_on_drop TINYINT(1) NOT NULL
        ');
    }
}
