<?php

namespace Innova\PathBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/01/10 11:35:29
 */
class Version20230110113528 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE innova_path 
            ADD success_message LONGTEXT DEFAULT NULL, 
            ADD failure_message LONGTEXT DEFAULT NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE innova_path 
            DROP success_message, 
            DROP failure_message
        ');
    }
}
