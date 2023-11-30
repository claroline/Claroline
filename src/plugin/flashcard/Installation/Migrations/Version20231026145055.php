<?php

namespace Claroline\FlashcardBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/10/26 02:50:57
 */
final class Version20231026145055 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE claro_flashcard_drawn_progression (
                id INT AUTO_INCREMENT NOT NULL, 
                flashcard_id INT DEFAULT NULL, 
                resource_evaluation_id INT DEFAULT NULL, 
                success_count INT NOT NULL, 
                INDEX IDX_BB81403DC5D16576 (flashcard_id), 
                INDEX IDX_BB81403DC807080 (resource_evaluation_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_flashcard_drawn_progression 
            ADD CONSTRAINT FK_BB81403DC5D16576 FOREIGN KEY (flashcard_id) 
            REFERENCES claro_flashcard_card (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_flashcard_drawn_progression 
            ADD CONSTRAINT FK_BB81403DC807080 FOREIGN KEY (resource_evaluation_id) 
            REFERENCES claro_resource_evaluation (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_flashcard_drawn_progression 
            DROP FOREIGN KEY FK_BB81403DC5D16576
        ');
        $this->addSql('
            ALTER TABLE claro_flashcard_drawn_progression 
            DROP FOREIGN KEY FK_BB81403DC807080
        ');
        $this->addSql('
            DROP TABLE claro_flashcard_drawn_progression
        ');
    }
}
