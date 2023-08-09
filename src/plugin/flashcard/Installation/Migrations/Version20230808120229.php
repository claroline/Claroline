<?php

namespace Claroline\FlashcardBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/08/08 12:02:30
 */
final class Version20230808120229 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE claro_flashcard_deck (
                id INT AUTO_INCREMENT NOT NULL, 
                end_back_target_id INT DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                show_overview TINYINT(1) NOT NULL, 
                description LONGTEXT DEFAULT NULL, 
                show_end_page TINYINT(1) NOT NULL, 
                end_message LONGTEXT DEFAULT NULL, 
                end_navigation TINYINT(1) NOT NULL, 
                end_back_type LONGTEXT DEFAULT NULL, 
                end_back_label LONGTEXT DEFAULT NULL, 
                show_workspace_certificates TINYINT(1) NOT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_62519606D17F50A6 (uuid), 
                UNIQUE INDEX UNIQ_62519606B87FAB32 (resourceNode_id), 
                INDEX IDX_6251960648FD0A1B (end_back_target_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_flashcard_card (
                id INT AUTO_INCREMENT NOT NULL, 
                deck_id INT NOT NULL, 
                question LONGTEXT DEFAULT NULL, 
                visibleContent LONGTEXT NOT NULL, 
                hiddenContent LONGTEXT NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_3BE938E2D17F50A6 (uuid), 
                INDEX IDX_3BE938E2111948DC (deck_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_flashcard_deck 
            ADD CONSTRAINT FK_62519606B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_flashcard_deck 
            ADD CONSTRAINT FK_6251960648FD0A1B FOREIGN KEY (end_back_target_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_flashcard_card 
            ADD CONSTRAINT FK_3BE938E2111948DC FOREIGN KEY (deck_id) 
            REFERENCES claro_flashcard_deck (id)
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_flashcard_deck 
            DROP FOREIGN KEY FK_62519606B87FAB32
        ');
        $this->addSql('
            ALTER TABLE claro_flashcard_deck 
            DROP FOREIGN KEY FK_6251960648FD0A1B
        ');
        $this->addSql('
            ALTER TABLE claro_flashcard_card 
            DROP FOREIGN KEY FK_3BE938E2111948DC
        ');
        $this->addSql('
            DROP TABLE claro_flashcard_deck
        ');
        $this->addSql('
            DROP TABLE claro_flashcard_card
        ');
    }
}
