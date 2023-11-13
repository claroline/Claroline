<?php

namespace Claroline\CoreBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/11/10 05:35:05
 */
final class Version20231110053448 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_resource_evaluation CHANGE progression progression DOUBLE PRECISION NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_resource_user_evaluation CHANGE progression progression DOUBLE PRECISION NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_evaluation CHANGE progression progression DOUBLE PRECISION NOT NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_resource_evaluation CHANGE progression progression INT NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_resource_user_evaluation CHANGE progression progression INT NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_evaluation CHANGE progression progression INT NOT NULL
        ');
    }
}
