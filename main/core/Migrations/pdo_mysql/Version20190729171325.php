<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2019/07/29 05:13:26
 */
class Version20190729171325 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_resource_user_evaluation 
            ADD required TINYINT(1) NOT NULL, 
            ADD progression_max INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_resource_evaluation 
            ADD progression_max INT DEFAULT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_resource_evaluation 
            DROP progression_max
        ');
        $this->addSql('
            ALTER TABLE claro_resource_user_evaluation 
            DROP required, 
            DROP progression_max
        ');
    }
}
