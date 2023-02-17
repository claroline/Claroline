<?php

namespace Claroline\ForumBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/02/16 10:26:09
 */
class Version20230216102556 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_forum 
            DROP maxComment,
            CHANGE show_overview show_overview TINYINT(1) NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_forum_subject 
            DROP author
        ');

        $this->addSql('
            UPDATE claro_forum_message AS m
            LEFT JOIN claro_forum_subject AS s ON m.subject_id = s.id
            LEFT JOIN claro_forum AS f ON s.forum_id = f.id
            SET m.moderation = "none"
            WHERE f.validationMode = "none"
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_forum 
            ADD maxComment INT NOT NULL,
            CHANGE show_overview show_overview TINYINT(1) DEFAULT "1" NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_forum_subject 
            ADD author VARCHAR(255) DEFAULT NULL
        ');
    }
}
