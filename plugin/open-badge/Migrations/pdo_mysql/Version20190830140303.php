<?php

namespace Claroline\OpenBadgeBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2019/08/30 02:03:08
 */
class Version20190830140303 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro__open_badge_evidence
            ADD rule_id INT DEFAULT NULL,
            ADD user_id INT DEFAULT NULL
        ');

        $this->addSql('
            CREATE INDEX IDX_6F68173744E0351 ON claro__open_badge_evidence (rule_id)
        ');

        $this->addSql('
            CREATE INDEX IDX_6F68173A76ED395 ON claro__open_badge_evidence (user_id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP INDEX IDX_6F68173744E0351 ON claro__open_badge_evidence
        ');
        $this->addSql('
            DROP INDEX IDX_6F68173A76ED395 ON claro__open_badge_evidence
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_evidence
            DROP rule_id
        ');
    }
}
