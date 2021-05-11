<?php

namespace Claroline\CoreBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/05/11 12:45:06
 */
class Version20210511124458 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_cryptographic_key 
            DROP FOREIGN KEY FK_1603A18232C8A3DE
        ');
        $this->addSql('
            ALTER TABLE claro_cryptographic_key 
            ADD CONSTRAINT FK_1603A18232C8A3DE FOREIGN KEY (organization_id) 
            REFERENCES claro__organization (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_cryptographic_key 
            DROP FOREIGN KEY FK_1603A18232C8A3DE
        ');
        $this->addSql('
            ALTER TABLE claro_cryptographic_key 
            ADD CONSTRAINT FK_1603A18232C8A3DE FOREIGN KEY (organization_id) 
            REFERENCES claro__organization (id)
        ');
    }
}
