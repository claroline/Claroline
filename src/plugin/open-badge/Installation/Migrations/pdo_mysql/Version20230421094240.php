<?php

namespace Claroline\OpenBadgeBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/04/21 09:42:54
 */
class Version20230421094240 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro__open_badge_assertion 
            DROP FOREIGN KEY FK_B6E0ABAD1623CB0A
        ');
        $this->addSql('
            DROP INDEX IDX_B6E0ABAD1623CB0A ON claro__open_badge_assertion
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_assertion 
            DROP verification_id
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro__open_badge_assertion 
            ADD verification_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_assertion 
            ADD CONSTRAINT FK_B6E0ABAD1623CB0A FOREIGN KEY (verification_id) 
            REFERENCES claro__open_badge_verification_object (id) ON UPDATE NO ACTION ON DELETE NO ACTION
        ');
        $this->addSql('
            CREATE INDEX IDX_B6E0ABAD1623CB0A ON claro__open_badge_assertion (verification_id)
        ');
    }
}
