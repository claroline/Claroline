<?php

namespace Claroline\CoreBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2024/05/16 11:39:40
 */
final class Version20240516113857 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_workspace 
            ADD description_html LONGTEXT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_resource_node 
            ADD description_html LONGTEXT DEFAULT NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_resource_node 
            DROP description_html
        ');
        $this->addSql('
            ALTER TABLE claro_workspace 
            DROP description_html
        ');
    }
}
