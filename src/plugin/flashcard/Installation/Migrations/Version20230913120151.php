<?php

namespace Claroline\FlashcardBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/09/13 12:01:51
 */
final class Version20230913120151 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_flashcard_card 
            ADD visibleContentType VARCHAR(255) NOT NULL, 
            ADD hiddenContentType VARCHAR(255) NOT NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_flashcard_card 
            DROP visibleContentType, 
            DROP hiddenContentType
        ');
    }
}
