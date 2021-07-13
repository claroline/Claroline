<?php

namespace Claroline\CoreBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/05/20 07:31:02
 */
class Version20210520073101 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE claro_template_content (
                id INT AUTO_INCREMENT NOT NULL, 
                template_id INT NOT NULL, 
                title VARCHAR(255) DEFAULT NULL, 
                content LONGTEXT DEFAULT NULL, 
                lang VARCHAR(255) NOT NULL, 
                INDEX IDX_1D5C077D5DA0FB8 (template_id), 
                UNIQUE INDEX template_unique_lang (template_id, lang), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB
        ');

        // move localization data in new table
        $this->addSql('
            INSERT INTO claro_template_content (template_id, title, content, lang)
                SELECT t2.id as template_id, t.title, t.content, t.lang
                FROM claro_template AS t
                LEFT JOIN (
                    SELECT MIN(t.id) as id, t.template_name
                    FROM claro_template AS t
                    GROUP BY t.template_name
                ) AS t2 ON (t.template_name = t2.template_name)
        ');

        $this->addSql('
            DELETE t1
            FROM claro_template AS t1, claro_template AS t2
            WHERE t1.id > t2.id AND t1.template_name = t2.template_name
        ');

        $this->addSql('
            ALTER TABLE claro_template_content 
            ADD CONSTRAINT FK_1D5C077D5DA0FB8 FOREIGN KEY (template_id) 
            REFERENCES claro_template (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            DROP INDEX template_unique_name ON claro_template
        ');
        $this->addSql('
            ALTER TABLE claro_template 
            DROP title, 
            DROP content, 
            DROP lang
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_DFB26A758BED546B ON claro_template (template_name)
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            DROP TABLE claro_template_content
        ');
        $this->addSql('
            DROP INDEX UNIQ_DFB26A758BED546B ON claro_template
        ');
        $this->addSql('
            ALTER TABLE claro_template 
            ADD title VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, 
            ADD content LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, 
            ADD lang VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`
        ');
        $this->addSql('
            CREATE UNIQUE INDEX template_unique_name ON claro_template (template_name, lang)
        ');
    }
}
