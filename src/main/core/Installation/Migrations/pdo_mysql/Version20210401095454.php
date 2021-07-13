<?php

namespace Claroline\CoreBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/04/01 09:54:55
 */
class Version20210401095454 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
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
            ) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB
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

    public function down(Schema $schema): void
    {
        $this->addSql('
            DROP TABLE claro_log_functionnal
        ');
    }
}
