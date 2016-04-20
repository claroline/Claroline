<?php

namespace Icap\WebsiteBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2014/09/02 01:42:57
 */
class Version20140902134256 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__website 
            ADD options_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE icap__website 
            ADD CONSTRAINT FK_452309F83ADB05F1 FOREIGN KEY (options_id) 
            REFERENCES icap__website_options (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_452309F83ADB05F1 ON icap__website (options_id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__website 
            DROP FOREIGN KEY FK_452309F83ADB05F1
        ');
        $this->addSql('
            DROP INDEX UNIQ_452309F83ADB05F1 ON icap__website
        ');
        $this->addSql('
            ALTER TABLE icap__website 
            DROP options_id
        ');
    }
}
