<?php

namespace Claroline\CoreBundle\Installation\Migrations\pdo_mysql;

use Claroline\MigrationBundle\Migrations\ConditionalMigrationTrait;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2022/11/18 08:36:03
 */
class Version20221118083553 extends AbstractMigration
{
    use ConditionalMigrationTrait;

    public function up(Schema $schema): void
    {
        // this is here because we need to handle a problem in plugins update order
        if (!$this->checkTableExists('claro_icon_item', $this->connection)) {
            // we are installing a new platform, no worry
            return;
        }

        // just a little hack because we no longer allow null value for name
        $this->addSql('
            UPDATE claro_icon_item SET `name` = "" WHERE `name` IS NULL 
        ');

        $this->addSql('
            ALTER TABLE claro_icon_item 
            CHANGE `name` entity_name VARCHAR(255) NOT NULL, 
            DROP class,
            ADD svg TINYINT(1) NOT NULL DEFAULT "0"
        ');
        $this->addSql('
            ALTER TABLE claro_icon_set 
            DROP is_active, 
            CHANGE name entity_name VARCHAR(255) NOT NULL, 
            DROP editable
        ');
    }

    public function down(Schema $schema): void
    {
    }
}
