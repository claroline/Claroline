<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2019/01/07 12:17:25
 */
class Version20190107121724 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_cryptographic_key (
                id INT AUTO_INCREMENT NOT NULL,
                user_id INT DEFAULT NULL,
                organization_id INT DEFAULT NULL,
                publicKeyParam LONGTEXT NOT NULL,
                privateKeyParam LONGTEXT DEFAULT NULL,
                uuid VARCHAR(36) NOT NULL,
                UNIQUE INDEX UNIQ_1603A182D17F50A6 (uuid), 
                INDEX IDX_1603A182A76ED395 (user_id),
                INDEX IDX_1603A18232C8A3DE (organization_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_cryptographic_key
            ADD CONSTRAINT FK_1603A182A76ED395 FOREIGN KEY (user_id)
            REFERENCES claro_user (id)
        ');
        $this->addSql('
            ALTER TABLE claro_cryptographic_key
            ADD CONSTRAINT FK_1603A18232C8A3DE FOREIGN KEY (organization_id)
            REFERENCES claro__organization (id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE claro_cryptographic_key
        ');
    }
}
