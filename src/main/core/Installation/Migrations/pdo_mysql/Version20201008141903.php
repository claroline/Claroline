<?php

namespace Claroline\CoreBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/10/08 02:19:26
 */
class Version20201008141903 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE claro_update (
                id INT AUTO_INCREMENT NOT NULL, 
                updater_class VARCHAR(255) NOT NULL, 
                UNIQUE INDEX UNIQ_6B1647E7CACF2BB1 (updater_class), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            DROP TABLE claro_update
        ');
    }
}
