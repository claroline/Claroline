<?php

namespace UJM\ExoBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2017/01/20 01:14:24
 */
class Version20170120131422 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_response 
            ADD feedback LONGTEXT DEFAULT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_response 
            DROP feedback
        ');
    }
}
