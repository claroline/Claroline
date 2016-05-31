<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2016/05/25 04:53:34
 */
class Version20160525165333 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_panel_facet_role (
                id INT AUTO_INCREMENT NOT NULL, 
                role_id INT NOT NULL, 
                canOpen TINYINT(1) NOT NULL, 
                canEdit TINYINT(1) NOT NULL, 
                panelFacet_id INT NOT NULL, 
                INDEX IDX_A66BF654D60322AC (role_id), 
                INDEX IDX_A66BF654E99038C0 (panelFacet_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_field_facet_choice (
                id INT AUTO_INCREMENT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                position INT NOT NULL, 
                fieldFacet_id INT NOT NULL, 
                INDEX IDX_E2001D9F9239AF (fieldFacet_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_panel_facet_role 
            ADD CONSTRAINT FK_A66BF654D60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_panel_facet_role 
            ADD CONSTRAINT FK_A66BF654E99038C0 FOREIGN KEY (panelFacet_id) 
            REFERENCES claro_panel_facet (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_field_facet_choice 
            ADD CONSTRAINT FK_E2001D9F9239AF FOREIGN KEY (fieldFacet_id) 
            REFERENCES claro_field_facet (id) 
            ON DELETE CASCADE
        ');
        $this->addSql("
            ALTER TABLE claro_field_facet_value 
            ADD arrayValue LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)'
        ");
        $this->addSql('
            ALTER TABLE claro_facet CHANGE isvisiblebyowner isMain TINYINT(1) NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_panel_facet CHANGE facet_id facet_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_field_facet 
            DROP isVisibleByOwner, 
            DROP isEditableByOwner
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE claro_panel_facet_role
        ');
        $this->addSql('
            DROP TABLE claro_field_facet_choice
        ');
        $this->addSql('
            ALTER TABLE claro_facet CHANGE ismain isVisibleByOwner TINYINT(1) NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_field_facet 
            ADD isVisibleByOwner TINYINT(1) NOT NULL, 
            ADD isEditableByOwner TINYINT(1) NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_field_facet_value 
            DROP arrayValue
        ');
        $this->addSql('
            ALTER TABLE claro_panel_facet CHANGE facet_id facet_id INT NOT NULL
        ');
    }
}
