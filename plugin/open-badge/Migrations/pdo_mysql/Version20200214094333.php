<?php

namespace Claroline\OpenBadgeBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/02/14 09:44:12
 */
class Version20200214094333 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro__open_badge_evidence 
            ADD workspaceEvidence_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_evidence 
            ADD CONSTRAINT FK_6F68173B9480AB1 FOREIGN KEY (workspaceEvidence_id) 
            REFERENCES claro_workspace_evaluation (id)
        ');
        $this->addSql('
            CREATE INDEX IDX_6F68173B9480AB1 ON claro__open_badge_evidence (workspaceEvidence_id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro__open_badge_evidence 
            DROP FOREIGN KEY FK_6F68173B9480AB1
        ');
        $this->addSql('
            DROP INDEX IDX_6F68173B9480AB1 ON claro__open_badge_evidence
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_evidence 
            DROP workspaceEvidence_id
        ');
    }
}
