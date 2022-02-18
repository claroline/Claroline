<?php

namespace Claroline\TransferBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2022/02/22 05:48:51
 */
class Version20220222174821 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_transfer_import 
            ADD file_format VARCHAR(255) NOT NULL,
            ADD extra LONGTEXT DEFAULT NULL COMMENT "(DC2Type:json)"
        ');
        $this->addSql('
            UPDATE claro_transfer_import SET file_format = "csv" 
        ');
        $this->addSql('
            ALTER TABLE claro_transfer_export 
            ADD file_format VARCHAR(255) NOT NULL,
            ADD extra LONGTEXT DEFAULT NULL COMMENT "(DC2Type:json)"
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_transfer_export 
            DROP file_format,
            DROP extra
        ');
        $this->addSql('
            ALTER TABLE claro_transfer_import 
            DROP file_format,
            DROP extra
        ');
    }
}
