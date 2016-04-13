<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/09/07 12:17:26
 */
class Version20150907121719 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_widget_roles (
                widget_id INT NOT NULL, 
                role_id INT NOT NULL, 
                INDEX IDX_D746FA2EFBE885E2 (widget_id), 
                INDEX IDX_D746FA2ED60322AC (role_id), 
                PRIMARY KEY(widget_id, role_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_widget_roles 
            ADD CONSTRAINT FK_D746FA2EFBE885E2 FOREIGN KEY (widget_id) 
            REFERENCES claro_widget (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_widget_roles 
            ADD CONSTRAINT FK_D746FA2ED60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE claro_widget_roles
        ');
    }
}
