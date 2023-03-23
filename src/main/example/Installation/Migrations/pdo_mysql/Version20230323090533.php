<?php

namespace Claroline\ExampleBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/03/23 09:05:49
 */
class Version20230323090533 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE claro_example_example (
                id INT AUTO_INCREMENT NOT NULL, 
                creator_id INT DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                entity_name VARCHAR(255) NOT NULL, 
                description LONGTEXT DEFAULT NULL, 
                createdAt DATETIME DEFAULT NULL, 
                updatedAt DATETIME DEFAULT NULL, 
                thumbnail VARCHAR(255) DEFAULT NULL, 
                poster VARCHAR(255) DEFAULT NULL, 
                UNIQUE INDEX UNIQ_B79B23D6D17F50A6 (uuid), 
                INDEX IDX_B79B23D661220EA6 (creator_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_example_example 
            ADD CONSTRAINT FK_B79B23D661220EA6 FOREIGN KEY (creator_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            DROP TABLE claro_example_example
        ');
    }
}
