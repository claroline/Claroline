<?php

namespace Claroline\LogBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/11/24 08:32:54
 */
final class Version20231124083242 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_log_functionnal 
            DROP FOREIGN KEY FK_29C2B64EA76ED395
        ');
        $this->addSql('
            DROP INDEX IDX_29C2B64EA76ED395 ON claro_log_functionnal
        ');
        $this->addSql('
            ALTER TABLE claro_log_functionnal 
            ADD doer_ip VARCHAR(255) DEFAULT NULL, 
            ADD doer_country VARCHAR(255) DEFAULT NULL, 
            ADD doer_city VARCHAR(255) DEFAULT NULL, 
            CHANGE user_id doer_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_log_functionnal 
            ADD CONSTRAINT FK_29C2B64E12D3860F FOREIGN KEY (doer_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            CREATE INDEX IDX_29C2B64E12D3860F ON claro_log_functionnal (doer_id)
        ');
        $this->addSql('
            ALTER TABLE claro_log_message 
            DROP FOREIGN KEY FK_5AC3989F624B39D
        ');
        $this->addSql('
            DROP INDEX IDX_5AC3989F624B39D ON claro_log_message
        ');
        $this->addSql('
            ALTER TABLE claro_log_message 
            ADD doer_ip VARCHAR(255) DEFAULT NULL, 
            ADD doer_country VARCHAR(255) DEFAULT NULL, 
            ADD doer_city VARCHAR(255) DEFAULT NULL, 
            CHANGE sender_id doer_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_log_message 
            ADD CONSTRAINT FK_5AC398912D3860F FOREIGN KEY (doer_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            CREATE INDEX IDX_5AC398912D3860F ON claro_log_message (doer_id)
        ');
        $this->addSql('
            ALTER TABLE claro_log_security 
            CHANGE doerIp doer_ip VARCHAR(255) DEFAULT NULL, 
            CHANGE country doer_country VARCHAR(255) DEFAULT NULL, 
            CHANGE city doer_city VARCHAR(255) DEFAULT NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_log_functionnal 
            DROP FOREIGN KEY FK_29C2B64E12D3860F
        ');
        $this->addSql('
            DROP INDEX IDX_29C2B64E12D3860F ON claro_log_functionnal
        ');
        $this->addSql('
            ALTER TABLE claro_log_functionnal 
            DROP doer_ip, 
            DROP doer_country, 
            DROP doer_city, 
            CHANGE doer_id user_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_log_functionnal 
            ADD CONSTRAINT FK_29C2B64EA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) ON UPDATE NO ACTION 
            ON DELETE SET NULL
        ');
        $this->addSql('
            CREATE INDEX IDX_29C2B64EA76ED395 ON claro_log_functionnal (user_id)
        ');
        $this->addSql('
            ALTER TABLE claro_log_message 
            DROP FOREIGN KEY FK_5AC398912D3860F
        ');
        $this->addSql('
            DROP INDEX IDX_5AC398912D3860F ON claro_log_message
        ');
        $this->addSql('
            ALTER TABLE claro_log_message 
            DROP doer_ip, 
            DROP doer_country, 
            DROP doer_city, 
            CHANGE doer_id sender_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_log_message 
            ADD CONSTRAINT FK_5AC3989F624B39D FOREIGN KEY (sender_id) 
            REFERENCES claro_user (id) ON UPDATE NO ACTION 
            ON DELETE SET NULL
        ');
        $this->addSql('
            CREATE INDEX IDX_5AC3989F624B39D ON claro_log_message (sender_id)
        ');
        $this->addSql('
            ALTER TABLE claro_log_security 
            ADD country VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, 
            ADD doerIp VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, 
            ADD city VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, 
            DROP doer_ip, 
            DROP doer_country, 
            DROP doer_city
        ');
    }
}
