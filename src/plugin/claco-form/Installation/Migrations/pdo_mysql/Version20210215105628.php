<?php

namespace Claroline\ClacoFormBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/02/15 10:56:42
 */
class Version20210215105628 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_clacoformbundle_claco_form 
            ADD showConfirm TINYINT(1) NOT NULL, 
            ADD confirmMessage LONGTEXT DEFAULT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_clacoformbundle_claco_form 
            DROP showConfirm, 
            DROP confirmMessage
        ');
    }
}
