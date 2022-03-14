<?php

namespace Claroline\AuthenticationBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2022/03/14 09:35:18
 */
class Version20220314093517 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_api_token 
            DROP FOREIGN KEY FK_2F3470B7A76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_api_token 
            ADD CONSTRAINT FK_2F3470B7A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_api_token 
            DROP FOREIGN KEY FK_2F3470B7A76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_api_token 
            ADD CONSTRAINT FK_2F3470B7A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ');
    }
}
