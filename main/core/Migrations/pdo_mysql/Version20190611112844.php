<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2019/06/11 11:28:45
 */
class Version20190611112844 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_api_token (
                id INT AUTO_INCREMENT NOT NULL,
                user_id INT DEFAULT NULL,
                token VARCHAR(36) NOT NULL,
                description LONGTEXT DEFAULT NULL,
                uuid VARCHAR(36) NOT NULL,
                UNIQUE INDEX UNIQ_2F3470B75F37A13B (token),
                UNIQUE INDEX UNIQ_2F3470B7D17F50A6 (uuid),
                INDEX IDX_2F3470B7A76ED395 (user_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_api_token
            ADD CONSTRAINT FK_2F3470B7A76ED395 FOREIGN KEY (user_id)
            REFERENCES claro_user (id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE claro_api_token
        ');
    }
}
