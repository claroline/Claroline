<?php

namespace Claroline\CursusBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/07/21 01:19:50
 */
class Version20210721131948 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE claro_cursusbundle_quota (
                id INT AUTO_INCREMENT NOT NULL, 
                organization_id INT NOT NULL, 
                threshold DOUBLE PRECISION NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_C4725F5FD17F50A6 (uuid), 
                UNIQUE INDEX `unique` (organization_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_quota 
            ADD CONSTRAINT FK_C4725F5F32C8A3DE FOREIGN KEY (organization_id) 
            REFERENCES claro__organization (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            DROP TABLE claro_cursusbundle_quota
        ');
    }
}
