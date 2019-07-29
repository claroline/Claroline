<?php

namespace Claroline\AudioPlayerBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2019/07/19 10:39:39
 */
class Version20190719103937 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_audio_resource_section 
            ADD title VARCHAR(255) DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_audio_params 
            ADD description LONGTEXT DEFAULT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_audio_params 
            DROP description
        ');
        $this->addSql('
            ALTER TABLE claro_audio_resource_section 
            DROP title
        ');
    }
}
