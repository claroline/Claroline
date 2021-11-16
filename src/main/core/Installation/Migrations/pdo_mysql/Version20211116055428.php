<?php

namespace Claroline\CoreBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/11/16 05:54:29
 */
class Version20211116055428 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            DROP INDEX name_idx ON claro_workspace
        ');
        $this->addSql('
            ALTER TABLE claro_workspace 
            ADD createdAt DATETIME DEFAULT NULL, 
            ADD updatedAt DATETIME DEFAULT NULL, 
            ADD hidden TINYINT(1) DEFAULT "0" NOT NULL,  
            CHANGE name entity_name VARCHAR(255) NOT NULL
        ');
        $this->addSql('
            UPDATE claro_workspace 
            SET 
                hidden = !displayable,
                createdAt = FROM_UNIXTIME(creation_date), 
                updatedAt = FROM_UNIXTIME(creation_date) 
        ');
        $this->addSql('
            ALTER TABLE claro_workspace
            DROP displayable,
            DROP creation_date
        ');
        $this->addSql('
            CREATE INDEX name_idx ON claro_workspace (entity_name)
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            DROP INDEX name_idx ON claro_workspace
        ');
        $this->addSql('
            ALTER TABLE claro_workspace 
            ADD displayable TINYINT(1) NOT NULL, 
            ADD creation_date INT DEFAULT NULL, 
            DROP createdAt, 
            DROP updatedAt, 
            DROP hidden, 
            CHANGE entity_name name VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`
        ');
        $this->addSql('
            CREATE INDEX name_idx ON claro_workspace (name)
        ');
    }
}
