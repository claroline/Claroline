<?php

namespace Innova\MediaResourceBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2016/06/21 11:20:02
 */
class Version20160621112001 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE media_resource_media (
                id INT AUTO_INCREMENT NOT NULL,
                media_resource_id INT NOT NULL,
                url VARCHAR(255) NOT NULL,
                type VARCHAR(255) NOT NULL,
                INDEX IDX_E0A22F7E7E5AEFB6 (media_resource_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE media_resource_help_link (
                id INT AUTO_INCREMENT NOT NULL,
                region_config_id INT NOT NULL,
                url VARCHAR(510) NOT NULL,
                INDEX IDX_F1D62D0C771B52B7 (region_config_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE media_resource_options (
                id INT AUTO_INCREMENT NOT NULL,
                mode VARCHAR(255) NOT NULL,
                showTextTranscription TINYINT(1) DEFAULT 0 NOT NULL, 
                ttsLanguage VARCHAR(5) NOT NULL,
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE media_resource (
                id INT AUTO_INCREMENT NOT NULL,
                options_id INT DEFAULT NULL,
                name VARCHAR(255) NOT NULL,
                resourceNode_id INT DEFAULT NULL,
                UNIQUE INDEX UNIQ_B3F0DAA43ADB05F1 (options_id),
                UNIQUE INDEX UNIQ_B3F0DAA4B87FAB32 (resourceNode_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE media_resource_region (
                id INT AUTO_INCREMENT NOT NULL,
                media_resource_id INT NOT NULL,
                start DOUBLE PRECISION NOT NULL,
                end DOUBLE PRECISION NOT NULL,
                note LONGTEXT DEFAULT NULL,
                uuid VARCHAR(255) NOT NULL,
                INDEX IDX_B1E36FE87E5AEFB6 (media_resource_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE media_resource_region_config (
                id INT AUTO_INCREMENT NOT NULL,
                region_id INT NOT NULL,
                has_loop TINYINT(1) NOT NULL,
                has_backward TINYINT(1) NOT NULL,
                has_rate TINYINT(1) NOT NULL,
                help_region_uuid VARCHAR(255) NOT NULL,
                UNIQUE INDEX UNIQ_2EEE09F098260155 (region_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE media_resource_help_text (
                id INT AUTO_INCREMENT NOT NULL,
                region_config_id INT NOT NULL,
                text VARCHAR(255) NOT NULL,
                INDEX IDX_FCF1133A771B52B7 (region_config_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE media_resource_media
            ADD CONSTRAINT FK_E0A22F7E7E5AEFB6 FOREIGN KEY (media_resource_id)
            REFERENCES media_resource (id)
        ');
        $this->addSql('
            ALTER TABLE media_resource_help_link
            ADD CONSTRAINT FK_F1D62D0C771B52B7 FOREIGN KEY (region_config_id)
            REFERENCES media_resource_region_config (id)
        ');
        $this->addSql('
            ALTER TABLE media_resource
            ADD CONSTRAINT FK_B3F0DAA43ADB05F1 FOREIGN KEY (options_id)
            REFERENCES media_resource_options (id)
        ');
        $this->addSql('
            ALTER TABLE media_resource
            ADD CONSTRAINT FK_B3F0DAA4B87FAB32 FOREIGN KEY (resourceNode_id)
            REFERENCES claro_resource_node (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE media_resource_region
            ADD CONSTRAINT FK_B1E36FE87E5AEFB6 FOREIGN KEY (media_resource_id)
            REFERENCES media_resource (id)
        ');
        $this->addSql('
            ALTER TABLE media_resource_region_config
            ADD CONSTRAINT FK_2EEE09F098260155 FOREIGN KEY (region_id)
            REFERENCES media_resource_region (id)
        ');
        $this->addSql('
            ALTER TABLE media_resource_help_text
            ADD CONSTRAINT FK_FCF1133A771B52B7 FOREIGN KEY (region_config_id)
            REFERENCES media_resource_region_config (id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE media_resource
            DROP FOREIGN KEY FK_B3F0DAA43ADB05F1
        ');
        $this->addSql('
            ALTER TABLE media_resource_media
            DROP FOREIGN KEY FK_E0A22F7E7E5AEFB6
        ');
        $this->addSql('
            ALTER TABLE media_resource_region
            DROP FOREIGN KEY FK_B1E36FE87E5AEFB6
        ');
        $this->addSql('
            ALTER TABLE media_resource_region_config
            DROP FOREIGN KEY FK_2EEE09F098260155
        ');
        $this->addSql('
            ALTER TABLE media_resource_help_link
            DROP FOREIGN KEY FK_F1D62D0C771B52B7
        ');
        $this->addSql('
            ALTER TABLE media_resource_help_text
            DROP FOREIGN KEY FK_FCF1133A771B52B7
        ');
        $this->addSql('
            DROP TABLE media_resource_media
        ');
        $this->addSql('
            DROP TABLE media_resource_help_link
        ');
        $this->addSql('
            DROP TABLE media_resource_options
        ');
        $this->addSql('
            DROP TABLE media_resource
        ');
        $this->addSql('
            DROP TABLE media_resource_region
        ');
        $this->addSql('
            DROP TABLE media_resource_region_config
        ');
        $this->addSql('
            DROP TABLE media_resource_help_text
        ');
    }
}
