<?php

namespace Claroline\AudioPlayerBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2019/10/02 02:31:07
 */
class Version20191002143105 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_audio_resource_section 
            ADD audio_description VARCHAR(255) DEFAULT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_audio_resource_section 
            DROP audio_description
        ');
    }
}
