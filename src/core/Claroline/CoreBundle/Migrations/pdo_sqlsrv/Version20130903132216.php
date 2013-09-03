<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/03 01:22:17
 */
class Version20130903132216 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            sp_RENAME 'claro_menu_action.permrequired', 
            'value', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE claro_menu_action ALTER COLUMN value NVARCHAR(255)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            sp_RENAME 'claro_menu_action.value', 
            'permRequired', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE claro_menu_action ALTER COLUMN permRequired NVARCHAR(255)
        ");
    }
}