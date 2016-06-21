<?php

namespace Innova\PathBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2016/02/01 09:04:53
 */
class Version20160201090451 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE StepConditionsGroup 
            ADD guid VARCHAR(255) NOT NULL
        ');
        $this->addSql('
            ALTER TABLE innova_step 
            ADD activity_height INT NOT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE StepConditionsGroup 
            DROP guid
        ');
        $this->addSql('
            ALTER TABLE innova_step 
            DROP activity_height
        ');
    }
}
