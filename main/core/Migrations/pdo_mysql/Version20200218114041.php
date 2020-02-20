<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/02/18 11:40:43
 */
class Version20200218114041 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_resource_user_evaluation 
            DROP custom_score
        ');
        $this->addSql('
            ALTER TABLE claro_resource_evaluation 
            DROP custom_score
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_evaluation 
            DROP custom_score
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_resource_evaluation 
            ADD custom_score VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci
        ');
        $this->addSql('
            ALTER TABLE claro_resource_user_evaluation 
            ADD custom_score VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_evaluation 
            ADD custom_score VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci
        ');
    }
}
