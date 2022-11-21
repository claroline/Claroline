<?php

namespace Claroline\ThemeBundle\Installation\Migrations\pdo_mysql;

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
        if (!$this->checkColumnExists('claro_icon_item', 'svg', $this->connection)) {
            // this is required because this migration also exist in CoreBundle
            // to handle problem in plugins update order
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
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_icon_item 
            CHANGE entity_name `name` VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, 
            ADD class VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`,
            DROP svg
        ');
        $this->addSql('
            ALTER TABLE claro_icon_set 
            ADD is_active TINYINT(1) NOT NULL, 
            CHANGE entity_name `name` VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, 
            ADD editable TINYINT(1) DEFAULT "0" NOT NULL
        ');
    }
}
