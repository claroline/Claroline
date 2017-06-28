<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2017/06/26 06:12:35
 */
class Version20170626181234 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_version (
                id INT AUTO_INCREMENT NOT NULL,
                commit VARCHAR(255) NOT NULL,
                version VARCHAR(255) NOT NULL, 
                branch VARCHAR(255) NOT NULL,
                bundle VARCHAR(255) NOT NULL,
                is_upgraded TINYINT(1) NOT NULL,
                date INT DEFAULT NULL,
                UNIQUE INDEX unique_version (version, bundle, branch),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE claro_version
        ');
    }
}
