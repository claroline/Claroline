<?php

namespace HeVinci\FavouriteBundle\Installation\Migrations\pdo_mysql;

use Claroline\MigrationBundle\Migrations\ConditionalMigrationTrait;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2022/03/14 09:28:59
 */
class Version20220314092857 extends AbstractMigration
{
    use ConditionalMigrationTrait;

    public function up(Schema $schema): void
    {
        if ($this->checkForeignKeyExists('FK_5ED1A9BDA76ED395', $this->connection)) {
            $this->addSql('
                ALTER TABLE claro_resource_favourite 
                DROP FOREIGN KEY FK_5ED1A9BDA76ED395
            ');
        }

        if ($this->checkForeignKeyExists('FK_55DB0452A76ED395', $this->connection)) {
            $this->addSql('
                ALTER TABLE claro_resource_favourite 
                DROP FOREIGN KEY FK_55DB0452A76ED395
            ');
        }
        $this->addSql('
            ALTER TABLE claro_resource_favourite 
            ADD CONSTRAINT FK_5ED1A9BDA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_favourite 
            DROP FOREIGN KEY FK_711A30BA76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_favourite 
            ADD CONSTRAINT FK_711A30BA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_resource_favourite 
            DROP FOREIGN KEY FK_5ED1A9BDA76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_resource_favourite 
            ADD CONSTRAINT FK_5ED1A9BDA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_favourite 
            DROP FOREIGN KEY FK_711A30BA76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_favourite 
            ADD CONSTRAINT FK_711A30BA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ');
    }
}
