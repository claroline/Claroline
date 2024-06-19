<?php

namespace Claroline\EvaluationBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2024/06/13 08:22:45
 */
final class Version20240613082244 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE claro_evaluation_certificate (
                id INT AUTO_INCREMENT NOT NULL, 
                evaluation_id INT DEFAULT NULL, 
                user_id INT DEFAULT NULL, 
                obtention_date DATETIME DEFAULT NULL, 
                issue_date DATETIME DEFAULT NULL, 
                content LONGTEXT DEFAULT NULL, 
                status VARCHAR(255) NOT NULL, 
                score DOUBLE PRECISION NOT NULL, 
                language VARCHAR(255) NOT NULL, 
                revoked TINYINT(1) DEFAULT 0 NOT NULL, 
                revocation_reason LONGTEXT DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_98CCC562D17F50A6 (uuid), 
                INDEX IDX_98CCC562456C5646 (evaluation_id), 
                INDEX IDX_98CCC562A76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_evaluation_certificate 
            ADD CONSTRAINT FK_98CCC562456C5646 FOREIGN KEY (evaluation_id) 
            REFERENCES claro_workspace_evaluation (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_evaluation_certificate 
            ADD CONSTRAINT FK_98CCC562A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_evaluation_certificate 
            DROP FOREIGN KEY FK_98CCC562456C5646
        ');
        $this->addSql('
            ALTER TABLE claro_evaluation_certificate 
            DROP FOREIGN KEY FK_98CCC562A76ED395
        ');
        $this->addSql('
            DROP TABLE claro_evaluation_certificate
        ');
    }
}
