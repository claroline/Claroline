<?php

namespace Icap\WikiBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/06/20 11:50:35
 */
class Version20180620115033 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__wiki 
            ADD display_contents TINYINT(1) DEFAULT '1' NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__wiki 
            DROP display_contents
        ');
    }
}
