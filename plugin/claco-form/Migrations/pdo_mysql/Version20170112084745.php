<?php

namespace Claroline\ClacoFormBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2017/01/12 08:47:47
 */
class Version20170112084745 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_clacoformbundle_claco_form_widget_config (
                id INT AUTO_INCREMENT NOT NULL, 
                claco_form_id INT DEFAULT NULL, 
                options LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', 
                widgetInstance_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_D1521187AB7B5A55 (widgetInstance_id), 
                INDEX IDX_D1521187F7D9CC0C (claco_form_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('
            CREATE TABLE claro_clacoformbundle_claco_form_widget_config_field (
                clacoformwidgetconfig_id INT NOT NULL, 
                field_id INT NOT NULL, 
                INDEX IDX_62B7ABB86C88DEB0 (clacoformwidgetconfig_id), 
                INDEX IDX_62B7ABB8443707B0 (field_id), 
                PRIMARY KEY(
                    clacoformwidgetconfig_id, field_id
                )
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_clacoformbundle_claco_form_widget_config 
            ADD CONSTRAINT FK_D1521187AB7B5A55 FOREIGN KEY (widgetInstance_id) 
            REFERENCES claro_widget_instance (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_clacoformbundle_claco_form_widget_config 
            ADD CONSTRAINT FK_D1521187F7D9CC0C FOREIGN KEY (claco_form_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_clacoformbundle_claco_form_widget_config_field 
            ADD CONSTRAINT FK_62B7ABB86C88DEB0 FOREIGN KEY (clacoformwidgetconfig_id) 
            REFERENCES claro_clacoformbundle_claco_form_widget_config (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_clacoformbundle_claco_form_widget_config_field 
            ADD CONSTRAINT FK_62B7ABB8443707B0 FOREIGN KEY (field_id) 
            REFERENCES claro_clacoformbundle_field (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_clacoformbundle_claco_form_widget_config_field 
            DROP FOREIGN KEY FK_62B7ABB86C88DEB0
        ');
        $this->addSql('
            DROP TABLE claro_clacoformbundle_claco_form_widget_config
        ');
        $this->addSql('
            DROP TABLE claro_clacoformbundle_claco_form_widget_config_field
        ');
    }
}
