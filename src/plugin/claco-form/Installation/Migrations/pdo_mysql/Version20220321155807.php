<?php

namespace Claroline\ClacoFormBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2022/03/21 03:58:09
 */
class Version20220321155807 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_clacoformbundle_entry 
            DROP FOREIGN KEY FK_889DAEDFA76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_clacoformbundle_entry 
            ADD CONSTRAINT FK_889DAEDFA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_clacoformbundle_entry 
            DROP FOREIGN KEY FK_889DAEDFA76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_clacoformbundle_entry 
            ADD CONSTRAINT FK_889DAEDFA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
    }
}
