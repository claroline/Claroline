<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/05/16 09:36:44
 */
class Version20180516093642 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_resource_user_evaluation 
            ADD progression INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_resource_evaluation 
            ADD progression INT DEFAULT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_resource_evaluation 
            DROP progression
        ');
        $this->addSql('
            ALTER TABLE claro_resource_user_evaluation 
            DROP progression
        ');
    }
}
