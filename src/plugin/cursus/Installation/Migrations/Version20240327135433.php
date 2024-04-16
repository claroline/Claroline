<?php

namespace Claroline\CursusBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2024/03/27 01:54:34
 */
final class Version20240327135433 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_presence_status 
            ADD presence_signature VARCHAR(255) DEFAULT NULL,
            ADD presence_validation_date DATETIME DEFAULT NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_presence_status 
            DROP presence_signature,
            DROP presence_validation_date
        ');
    }
}
