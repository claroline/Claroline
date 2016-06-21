<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2016/03/22 04:25:37
 */
class Version20160322162536 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_additonal_action (
                id INT AUTO_INCREMENT NOT NULL, 
                action VARCHAR(255) NOT NULL, 
                displayedName VARCHAR(255) NOT NULL, 
                class VARCHAR(255) NOT NULL, 
                type VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE claro_additonal_action
        ');
    }
}
