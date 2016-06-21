<?php

namespace Innova\PathBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2016/01/11 12:52:37
 */
class Version20160111125236 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE innova_path_widget_config_tags (
                widget_config_id INT NOT NULL, 
                tag_id INT NOT NULL, 
                INDEX IDX_95075D98685E7B00 (widget_config_id), 
                INDEX IDX_95075D98BAD26311 (tag_id), 
                PRIMARY KEY(widget_config_id, tag_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE innova_path_widget_config_tags 
            ADD CONSTRAINT FK_95075D98685E7B00 FOREIGN KEY (widget_config_id) 
            REFERENCES innova_path_widget_config (id)
        ');
        $this->addSql('
            ALTER TABLE innova_path_widget_config_tags 
            ADD CONSTRAINT FK_95075D98BAD26311 FOREIGN KEY (tag_id) 
            REFERENCES claro_tagbundle_tag (id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE innova_path_widget_config_tags
        ');
    }
}
