<?php

namespace Claroline\OpenBadgeBundle\Installation\Migrations\pdo_mysql;

use Claroline\MigrationBundle\Migrations\ConditionalMigrationTrait;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/04/21 08:37:49
 */
class Version20230426080000 extends AbstractMigration
{
    use ConditionalMigrationTrait;

    public function up(Schema $schema): void
    {
        if ($this->checkTableExists('revocationlist_assertion', $this->connection)) {
            $this->addSql('ALTER TABLE revocationlist_assertion DROP FOREIGN KEY FK_FDE09CC0412E672C');
        }

        if ($this->checkTableExists('claro__open_badge_endorsement', $this->connection)) {
            $this->addSql('ALTER TABLE claro__open_badge_endorsement DROP FOREIGN KEY FK_F2235FAE1623CB0A');
        }

        $this->addSql('DROP TABLE IF EXISTS badgeclass_group');
        $this->addSql('DROP TABLE IF EXISTS badgeclass_user');
        $this->addSql('DROP TABLE IF EXISTS claro__open_badge_endorsement');
        $this->addSql('DROP TABLE IF EXISTS claro__open_badge_identity_object');
        $this->addSql('DROP TABLE IF EXISTS claro__open_badge_revocation_list');
        $this->addSql('DROP TABLE IF EXISTS claro__open_badge_signed_badge');
        $this->addSql('DROP TABLE IF EXISTS claro__open_badge_verification_object');
        $this->addSql('DROP TABLE IF EXISTS revocationlist_assertion');
    }

    public function down(Schema $schema): void
    {
    }
}
