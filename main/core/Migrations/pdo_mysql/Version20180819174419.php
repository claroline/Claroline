<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/08/19 05:44:23
 */
class Version20180819174419 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_widget_instance 
            ADD dataSource_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_widget_instance 
            ADD CONSTRAINT FK_5F89A385F3D3127E FOREIGN KEY (dataSource_id) 
            REFERENCES claro_data_source (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE INDEX IDX_5F89A385F3D3127E ON claro_widget_instance (dataSource_id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_widget_instance 
            DROP FOREIGN KEY FK_5F89A385F3D3127E
        ');
        $this->addSql('
            DROP INDEX IDX_5F89A385F3D3127E ON claro_widget_instance
        ');
        $this->addSql('
            ALTER TABLE claro_widget_instance 
            DROP dataSource_id
        ');
    }
}
