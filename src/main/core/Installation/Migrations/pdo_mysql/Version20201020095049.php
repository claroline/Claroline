<?php

namespace Claroline\CoreBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/10/20 09:50:50
 */
class Version20201020095049 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_file 
            ADD opening VARCHAR(255) NOT NULL
        ');

        $this->addSql('
            UPDATE claro_file SET opening = "player" WHERE autoDownload = false
        ');
        $this->addSql('
            UPDATE claro_file SET opening = "download" WHERE autoDownload = true
        ');
        $this->addSql('
            ALTER TABLE claro_file 
            DROP autoDownload
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_file 
            ADD autoDownload TINYINT(1) NOT NULL, 
            DROP opening
        ');
    }
}
