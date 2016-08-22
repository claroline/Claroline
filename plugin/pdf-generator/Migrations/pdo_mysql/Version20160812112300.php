<?php

namespace Claroline\PdfGeneratorBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2016/08/12 11:23:02
 */
class Version20160812112300 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_pdf_generator (
                id INT AUTO_INCREMENT NOT NULL,
                creator_id INT NOT NULL,
                creation_date DATETIME NOT NULL,
                guid VARCHAR(255) NOT NULL,
                path VARCHAR(255) NOT NULL,
                name VARCHAR(255) NOT NULL,
                UNIQUE INDEX UNIQ_6F3552112B6FCFB2 (guid),
                UNIQUE INDEX UNIQ_6F355211B548B0F (path),
                INDEX IDX_6F35521161220EA6 (creator_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_pdf_generator
            ADD CONSTRAINT FK_6F35521161220EA6 FOREIGN KEY (creator_id)
            REFERENCES claro_user (id)
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE claro_pdf_generator
        ');
    }
}
