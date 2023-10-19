<?php

namespace Claroline\FlashcardBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/10/17 01:51:34
 */
final class Version20231017135132 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_flashcard_deck 
            ADD customButtons TINYINT(1) NOT NULL, 
            ADD rightButtonLabel VARCHAR(255) DEFAULT NULL, 
            ADD wrongButtonLabel VARCHAR(255) DEFAULT NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_flashcard_deck 
            DROP customButtons, 
            DROP rightButtonLabel, 
            DROP wrongButtonLabel
        ');
    }
}
