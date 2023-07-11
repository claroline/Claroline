<?php

namespace Icap\BibliographyBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/07/10 01:57:55
 */
final class Version20180628110402 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE icap__bibliography_book_reference (
                id INT AUTO_INCREMENT NOT NULL, 
                author VARCHAR(255) DEFAULT NULL, 
                description LONGTEXT DEFAULT NULL, 
                abstract LONGTEXT DEFAULT NULL, 
                isbn VARCHAR(14) DEFAULT NULL, 
                publisher VARCHAR(255) DEFAULT NULL, 
                printer VARCHAR(255) DEFAULT NULL, 
                publicationYear INT DEFAULT NULL, 
                language VARCHAR(255) DEFAULT NULL, 
                pageCount INT DEFAULT NULL, 
                url VARCHAR(255) DEFAULT NULL, 
                coverUrl VARCHAR(255) DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_D961F495D17F50A6 (uuid), 
                UNIQUE INDEX UNIQ_D961F495B87FAB32 (resourceNode_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE icap__bibliography_book_reference_configuration (
                id INT AUTO_INCREMENT NOT NULL, 
                api_key VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE icap__bibliography_book_reference 
            ADD CONSTRAINT FK_D961F495B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE icap__bibliography_book_reference 
            DROP FOREIGN KEY FK_D961F495B87FAB32
        ');
        $this->addSql('
            DROP TABLE icap__bibliography_book_reference
        ');
        $this->addSql('
            DROP TABLE icap__bibliography_book_reference_configuration
        ');
    }
}
