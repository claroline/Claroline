<?php

namespace Claroline\CoreBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/02/09 05:59:52
 */
class Version20230209055938 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            UPDATE claro_resource_evaluation SET progression = 0 WHERE progression IS NULL
        ');
        $this->addSql('
            ALTER TABLE claro_resource_evaluation CHANGE progression progression INT NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_resource_user_evaluation 
            DROP FOREIGN KEY FK_BCA02E7AA76ED395
        ');
        $this->addSql('
            UPDATE claro_resource_user_evaluation SET progression = 0 WHERE progression IS NULL
        ');
        $this->addSql('
            ALTER TABLE claro_resource_user_evaluation 
            DROP user_name, 
            CHANGE progression progression INT NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_resource_user_evaluation 
            ADD CONSTRAINT FK_BCA02E7AA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_evaluation 
            DROP FOREIGN KEY FK_E0FF675482D40A1F
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_evaluation 
            DROP FOREIGN KEY FK_E0FF6754A76ED395
        ');
        $this->addSql('
            UPDATE claro_workspace_evaluation SET progression = 0 WHERE progression IS NULL
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_evaluation 
            DROP workspace_code, 
            DROP user_name, 
            CHANGE progression progression INT NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_evaluation 
            ADD CONSTRAINT FK_E0FF675482D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_evaluation 
            ADD CONSTRAINT FK_E0FF6754A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_resource_evaluation CHANGE progression progression INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_resource_user_evaluation 
            DROP FOREIGN KEY FK_BCA02E7AA76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_resource_user_evaluation 
            ADD user_name VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, 
            CHANGE progression progression INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_resource_user_evaluation 
            ADD CONSTRAINT FK_BCA02E7AA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_evaluation 
            DROP FOREIGN KEY FK_E0FF675482D40A1F
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_evaluation 
            DROP FOREIGN KEY FK_E0FF6754A76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_evaluation 
            ADD workspace_code VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, 
            ADD user_name VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, 
            CHANGE progression progression INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_evaluation 
            ADD CONSTRAINT FK_E0FF675482D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_evaluation 
            ADD CONSTRAINT FK_E0FF6754A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
    }
}
