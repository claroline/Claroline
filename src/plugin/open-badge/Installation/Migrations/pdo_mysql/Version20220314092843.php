<?php

namespace Claroline\OpenBadgeBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2022/03/14 09:28:45
 */
class Version20220314092843 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro__open_badge_assertion 
            DROP FOREIGN KEY FK_B6E0ABADE92F8F78
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_assertion 
            ADD CONSTRAINT FK_B6E0ABADE92F8F78 FOREIGN KEY (recipient_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');

        $this->addSql('
            ALTER TABLE claro__open_badge_evidence 
            DROP FOREIGN KEY FK_6F68173A76ED395
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_evidence 
            ADD CONSTRAINT FK_6F68173A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro__open_badge_assertion 
            DROP FOREIGN KEY FK_B6E0ABADE92F8F78
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_assertion 
            ADD CONSTRAINT FK_B6E0ABADE92F8F78 FOREIGN KEY (recipient_id) 
            REFERENCES claro_user (id)
        ');

        $this->addSql('
            ALTER TABLE claro__open_badge_evidence 
            DROP FOREIGN KEY FK_6F68173A76ED395
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_evidence 
            ADD CONSTRAINT FK_6F68173A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ');
    }
}
