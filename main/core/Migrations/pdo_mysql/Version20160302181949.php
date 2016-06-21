<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2016/03/02 06:19:50
 */
class Version20160302181949 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_user_administrator 
            DROP PRIMARY KEY
        ');
        $this->addSql('
            ALTER TABLE claro_user_administrator 
            ADD PRIMARY KEY (user_id, organization_id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_user_administrator 
            DROP PRIMARY KEY
        ');
        $this->addSql('
            ALTER TABLE claro_user_administrator 
            ADD PRIMARY KEY (organization_id, user_id)
        ');
    }
}
