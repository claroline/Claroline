<?php

namespace Claroline\TransferBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2022/05/23 07:06:07
 */
class Version20220523070605 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_transfer_export 
            ADD name VARCHAR(255) DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_transfer_import 
            ADD name VARCHAR(255) DEFAULT NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_transfer_export 
            DROP name
        ');
        $this->addSql('
            ALTER TABLE claro_transfer_import 
            DROP name
        ');
    }
}
