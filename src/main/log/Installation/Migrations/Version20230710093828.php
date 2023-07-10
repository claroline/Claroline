<?php

namespace Claroline\LogBundle\Installation\Migrations;

use Claroline\MigrationBundle\Migrations\ConditionalMigrationTrait;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/07/10 09:38:28
 */
final class Version20230710093828 extends AbstractMigration
{
    use ConditionalMigrationTrait;

    public function up(Schema $schema): void
    {
        if (!$this->checkTableExists('claro_log_functionnal', $this->connection)) {
            $this->addSql('
                CREATE TABLE claro_log_functionnal (
                    id INT AUTO_INCREMENT NOT NULL, 
                    user_id INT DEFAULT NULL, 
                    resource_id INT DEFAULT NULL, 
                    workspace_id INT DEFAULT NULL, 
                    date DATETIME NOT NULL, 
                    details LONGTEXT NOT NULL, 
                    event VARCHAR(255) NOT NULL, 
                    INDEX IDX_29C2B64EA76ED395 (user_id), 
                    INDEX IDX_29C2B64E89329D25 (resource_id), 
                    INDEX IDX_29C2B64E82D40A1F (workspace_id), 
                    PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
            ');

            $this->addSql('
                ALTER TABLE claro_log_functionnal 
                ADD CONSTRAINT FK_29C2B64EA76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) 
                ON DELETE SET NULL
            ');
            $this->addSql('
                ALTER TABLE claro_log_functionnal 
                ADD CONSTRAINT FK_29C2B64E89329D25 FOREIGN KEY (resource_id) 
                REFERENCES claro_resource_node (id) 
                ON DELETE SET NULL
            ');
            $this->addSql('
                ALTER TABLE claro_log_functionnal 
                ADD CONSTRAINT FK_29C2B64E82D40A1F FOREIGN KEY (workspace_id) 
                REFERENCES claro_workspace (id) 
                ON DELETE SET NULL
            ');
        }

        if (!$this->checkTableExists('claro_log_message', $this->connection)) {
            $this->addSql('
                CREATE TABLE claro_log_message (
                    id INT AUTO_INCREMENT NOT NULL, 
                    receiver_id INT DEFAULT NULL, 
                    sender_id INT DEFAULT NULL, 
                    date DATETIME NOT NULL, 
                    details LONGTEXT NOT NULL, 
                    event VARCHAR(255) NOT NULL, 
                    INDEX IDX_5AC3989CD53EDB6 (receiver_id), 
                    INDEX IDX_5AC3989F624B39D (sender_id), 
                    PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
            ');

            $this->addSql('
                ALTER TABLE claro_log_message 
                ADD CONSTRAINT FK_5AC3989CD53EDB6 FOREIGN KEY (receiver_id) 
                REFERENCES claro_user (id) 
                ON DELETE SET NULL
            ');
            $this->addSql('
                ALTER TABLE claro_log_message 
                ADD CONSTRAINT FK_5AC3989F624B39D FOREIGN KEY (sender_id) 
                REFERENCES claro_user (id) 
                ON DELETE SET NULL
            ');
        }

        if (!$this->checkTableExists('claro_log_security', $this->connection)) {
            $this->addSql('
                CREATE TABLE claro_log_security (
                    id INT AUTO_INCREMENT NOT NULL, 
                    target_id INT DEFAULT NULL, 
                    doer_id INT DEFAULT NULL, 
                    country VARCHAR(255) DEFAULT NULL, 
                    doerIp VARCHAR(255) DEFAULT NULL, 
                    city VARCHAR(255) DEFAULT NULL, 
                    date DATETIME NOT NULL, 
                    details LONGTEXT NOT NULL, 
                    event VARCHAR(255) NOT NULL, 
                    INDEX IDX_91F693E1158E0B66 (target_id), 
                    INDEX IDX_91F693E112D3860F (doer_id), 
                    PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
            ');

            $this->addSql('
                ALTER TABLE claro_log_security 
                ADD CONSTRAINT FK_91F693E1158E0B66 FOREIGN KEY (target_id) 
                REFERENCES claro_user (id) 
                ON DELETE SET NULL
            ');
            $this->addSql('
                ALTER TABLE claro_log_security 
                ADD CONSTRAINT FK_91F693E112D3860F FOREIGN KEY (doer_id) 
                REFERENCES claro_user (id) 
                ON DELETE SET NULL
            ');
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_log_functionnal 
            DROP FOREIGN KEY FK_29C2B64EA76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_log_functionnal 
            DROP FOREIGN KEY FK_29C2B64E89329D25
        ');
        $this->addSql('
            ALTER TABLE claro_log_functionnal 
            DROP FOREIGN KEY FK_29C2B64E82D40A1F
        ');
        $this->addSql('
            ALTER TABLE claro_log_message 
            DROP FOREIGN KEY FK_5AC3989CD53EDB6
        ');
        $this->addSql('
            ALTER TABLE claro_log_message 
            DROP FOREIGN KEY FK_5AC3989F624B39D
        ');
        $this->addSql('
            ALTER TABLE claro_log_security 
            DROP FOREIGN KEY FK_91F693E1158E0B66
        ');
        $this->addSql('
            ALTER TABLE claro_log_security 
            DROP FOREIGN KEY FK_91F693E112D3860F
        ');
        $this->addSql('
            DROP TABLE claro_log_functionnal
        ');
        $this->addSql('
            DROP TABLE claro_log_message
        ');
        $this->addSql('
            DROP TABLE claro_log_security
        ');
    }
}
