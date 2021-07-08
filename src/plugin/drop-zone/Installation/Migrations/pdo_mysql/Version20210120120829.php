<?php

namespace Claroline\DropZoneBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/01/20 12:08:30
 */
class Version20210120120829 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_dropzonebundle_dropzone 
            ADD lock_drops TINYINT(1) NOT NULL
        ');

        $this->addSql('
            UPDATE claro_dropzonebundle_dropzone SET lock_drops = 0
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_dropzonebundle_dropzone 
            DROP lock_drops
        ');
    }
}
