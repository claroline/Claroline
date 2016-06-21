<?php

namespace Claroline\ScormBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/08/27 05:10:40
 */
class Version20150827171038 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_scorm_2004_resource CHANGE hash_name hash_name VARCHAR(255) NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_scorm_12_resource CHANGE hash_name hash_name VARCHAR(255) NOT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_scorm_12_resource CHANGE hash_name hash_name VARCHAR(50) NOT NULL COLLATE utf8_unicode_ci
        ');
        $this->addSql('
            ALTER TABLE claro_scorm_2004_resource CHANGE hash_name hash_name VARCHAR(50) NOT NULL COLLATE utf8_unicode_ci
        ');
    }
}
