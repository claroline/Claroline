<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2017/05/31 08:07:44
 */
class Version20170624000000 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_theme 
            ADD uuid VARCHAR(36) NOT NULL,
            ADD description LONGTEXT DEFAULT NULL,
            ADD is_default TINYINT(1) NOT NULL,
            ADD enabled TINYINT(1) NOT NULL,
            ADD user_id INT DEFAULT NULL
        ');

        $this->addSql('
            ALTER TABLE claro_theme 
            ADD CONSTRAINT FK_1D76301AA76ED395 
            FOREIGN KEY (user_id) REFERENCES claro_user (id) ON DELETE CASCADE
        ');

        // The new column needs to be filled to be able to add the UNIQUE constraint
        $this->addSql('
            UPDATE claro_theme SET uuid = (SELECT UUID())
        ');

        $this->addSql('
            UPDATE claro_theme SET enabled=true, is_default=false
        ');

        // Create the new default theme
        $this->addSql('
            UPDATE claro_theme SET is_default=true WHERE name="Claroline"
        ');

        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_1D76301AD17F50A6 ON claro_theme (uuid)
        ');

        $this->addSql('
            CREATE INDEX IDX_1D76301AA76ED395 ON claro_theme (user_id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP INDEX IDX_1D76301AA76ED395 ON claro_theme
        ');

        $this->addSql('
            DROP INDEX UNIQ_1D76301AD17F50A6 ON claro_theme
        ');

        $this->addSql('
            ALTER TABLE claro_theme 
            DROP uuid
        ');
    }
}
