<?php

namespace Claroline\FlashcardBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/08/11 12:37:32
 */
final class Version20230811123731 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE claro_flashcard_progression (
                id INT AUTO_INCREMENT NOT NULL, 
                flashcard_id INT DEFAULT NULL, 
                user_id INT DEFAULT NULL, 
                is_successful TINYINT(1) NOT NULL, 
                INDEX IDX_20460C74C5D16576 (flashcard_id), 
                INDEX IDX_20460C74A76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_flashcard_progression 
            ADD CONSTRAINT FK_20460C74C5D16576 FOREIGN KEY (flashcard_id) 
            REFERENCES claro_flashcard_card (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_flashcard_progression 
            ADD CONSTRAINT FK_20460C74A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_flashcard_progression 
            DROP FOREIGN KEY FK_20460C74C5D16576
        ');
        $this->addSql('
            ALTER TABLE claro_flashcard_progression 
            DROP FOREIGN KEY FK_20460C74A76ED395
        ');
        $this->addSql('
            DROP TABLE claro_flashcard_progression
        ');
    }
}
