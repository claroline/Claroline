<?php

namespace Icap\WebsiteBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2017/11/23 01:06:46
 */
class Version20171123130644 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__website_page 
            DROP FOREIGN KEY FK_FB66D1D41BAD783F
        ');
        $this->addSql('
            ALTER TABLE icap__website_page 
            ADD CONSTRAINT FK_FB66D1D41BAD783F FOREIGN KEY (resource_node_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE SET NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__website_page 
            DROP FOREIGN KEY FK_FB66D1D41BAD783F
        ');
        $this->addSql('
            ALTER TABLE icap__website_page 
            ADD CONSTRAINT FK_FB66D1D41BAD783F FOREIGN KEY (resource_node_id) 
            REFERENCES claro_resource_node (id)
        ');
    }
}
