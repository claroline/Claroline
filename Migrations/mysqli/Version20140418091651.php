<?php

namespace Claroline\CoreBundle\Migrations\mysqli;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/04/18 09:16:52
 */
class Version20140418091651 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_resource_node 
            DROP FOREIGN KEY FK_A76799FF54B9D732
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            ADD CONSTRAINT FK_A76799FF54B9D732 FOREIGN KEY (icon_id) 
            REFERENCES claro_resource_icon (id) 
            ON DELETE SET NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_resource_node 
            DROP FOREIGN KEY FK_A76799FF54B9D732
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            ADD CONSTRAINT FK_A76799FF54B9D732 FOREIGN KEY (icon_id) 
            REFERENCES claro_resource_icon (id) 
            ON DELETE CASCADE
        ");
    }
}