<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/02/15 05:05:18
 */
class Version20180215170517 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE user_organization
            DROP FOREIGN KEY FK_41221F7EA76ED395
        ');
        $this->addSql('
            ALTER TABLE user_organization
            DROP FOREIGN KEY FK_41221F7EF35E13B7
        ');
        $this->addSql('
            ALTER TABLE user_organization
            ADD CONSTRAINT FK_41221F7EA76ED395 FOREIGN KEY (user_id)
            REFERENCES claro_user (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE user_organization
            ADD CONSTRAINT FK_41221F7EF35E13B7 FOREIGN KEY (oganization_id)
            REFERENCES claro__organization (id)
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE user_organization
            DROP FOREIGN KEY FK_41221F7EA76ED395
        ');
        $this->addSql('
            ALTER TABLE user_organization
            DROP FOREIGN KEY FK_41221F7EF35E13B7
        ');
        $this->addSql('
            ALTER TABLE user_organization
            ADD CONSTRAINT FK_41221F7EA76ED395 FOREIGN KEY (user_id)
            REFERENCES claro_user (id)
        ');
        $this->addSql('
            ALTER TABLE user_organization
            ADD CONSTRAINT FK_41221F7EF35E13B7 FOREIGN KEY (oganization_id)
            REFERENCES claro__organization (id)
        ');
    }
}
