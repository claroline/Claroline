<?php

namespace HeVinci\UrlBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/12/08 09:05:51
 */
class Version20211208090550 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE hevinci_url 
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE hevinci_url SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_A3D1D452D17F50A6 ON hevinci_url (uuid)
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            DROP INDEX UNIQ_A3D1D452D17F50A6 ON hevinci_url
        ');
        $this->addSql('
            ALTER TABLE hevinci_url 
            DROP uuid
        ');
    }
}
