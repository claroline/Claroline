<?php

namespace Claroline\ClacoFormBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/04/21 08:44:06
 */
class Version20230421084352 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_clacoformbundle_category CHANGE details details LONGTEXT DEFAULT NULL COMMENT "(DC2Type:json)"
        ');
        $this->addSql('
            ALTER TABLE claro_clacoformbundle_claco_form CHANGE details details LONGTEXT DEFAULT NULL COMMENT "(DC2Type:json)"
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_clacoformbundle_category CHANGE details details LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci` COMMENT "(DC2Type:json_array)"
        ');
        $this->addSql('
            ALTER TABLE claro_clacoformbundle_claco_form CHANGE details details LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci` COMMENT "(DC2Type:json_array)"
        ');
    }
}
