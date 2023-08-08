<?php

namespace Claroline\FlashcardBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/08/07 02:28:33
 */
final class Version20230807142832 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("
            CREATE TABLE claro_flashcard_card (
                id INT AUTO_INCREMENT NOT NULL, 
                deck_id INT DEFAULT NULL, 
                question LONGTEXT DEFAULT NULL, 
                visibleContent LONGTEXT NOT NULL, 
                hiddenContent LONGTEXT NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_3BE938E2D17F50A6 (uuid), 
                INDEX IDX_3BE938E2111948DC (deck_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE claro_flashcard_card 
            ADD CONSTRAINT FK_3BE938E2111948DC FOREIGN KEY (deck_id) 
            REFERENCES claro_flashcard_deck (id)
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("
            ALTER TABLE claro_flashcard_card 
            DROP FOREIGN KEY FK_3BE938E2111948DC
        ");
        $this->addSql("
            DROP TABLE claro_flashcard_card
        ");
    }
}
