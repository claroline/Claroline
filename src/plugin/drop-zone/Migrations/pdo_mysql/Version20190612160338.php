<?php

namespace Claroline\DropZoneBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2019/06/12 04:03:39
 */
class Version20190612160338 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_dropzonebundle_revision (
                id INT AUTO_INCREMENT NOT NULL, 
                drop_id INT NOT NULL, 
                creator_id INT DEFAULT NULL, 
                creation_date DATETIME NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_5D4C5512D17F50A6 (uuid), 
                INDEX IDX_5D4C55124D224760 (drop_id), 
                INDEX IDX_5D4C551261220EA6 (creator_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_dropzonebundle_revision_comment (
                id INT AUTO_INCREMENT NOT NULL, 
                revision_id INT DEFAULT NULL, 
                user_id INT DEFAULT NULL, 
                content LONGTEXT NOT NULL, 
                creation_date DATETIME NOT NULL, 
                edition_date DATETIME DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_1756823AD17F50A6 (uuid), 
                INDEX IDX_1756823A1DFA7C8F (revision_id), 
                INDEX IDX_1756823AA76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_dropzonebundle_drop_comment (
                id INT AUTO_INCREMENT NOT NULL, 
                drop_id INT DEFAULT NULL, 
                user_id INT DEFAULT NULL, 
                content LONGTEXT NOT NULL, 
                creation_date DATETIME NOT NULL, 
                edition_date DATETIME DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_214AC1D1D17F50A6 (uuid), 
                INDEX IDX_214AC1D14D224760 (drop_id), 
                INDEX IDX_214AC1D1A76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_dropzonebundle_revision 
            ADD CONSTRAINT FK_5D4C55124D224760 FOREIGN KEY (drop_id) 
            REFERENCES claro_dropzonebundle_drop (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_dropzonebundle_revision 
            ADD CONSTRAINT FK_5D4C551261220EA6 FOREIGN KEY (creator_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_dropzonebundle_revision_comment 
            ADD CONSTRAINT FK_1756823A1DFA7C8F FOREIGN KEY (revision_id) 
            REFERENCES claro_dropzonebundle_revision (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_dropzonebundle_revision_comment 
            ADD CONSTRAINT FK_1756823AA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_dropzonebundle_drop_comment 
            ADD CONSTRAINT FK_214AC1D14D224760 FOREIGN KEY (drop_id) 
            REFERENCES claro_dropzonebundle_drop (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_dropzonebundle_drop_comment 
            ADD CONSTRAINT FK_214AC1D1A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_dropzonebundle_document 
            ADD revision_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_dropzonebundle_document 
            ADD CONSTRAINT FK_E846CAA81DFA7C8F FOREIGN KEY (revision_id) 
            REFERENCES claro_dropzonebundle_revision (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            CREATE INDEX IDX_E846CAA81DFA7C8F ON claro_dropzonebundle_document (revision_id)
        ');
        $this->addSql('
            ALTER TABLE claro_dropzonebundle_dropzone 
            ADD revision_enabled TINYINT(1) NOT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_dropzonebundle_document 
            DROP FOREIGN KEY FK_E846CAA81DFA7C8F
        ');
        $this->addSql('
            ALTER TABLE claro_dropzonebundle_revision_comment 
            DROP FOREIGN KEY FK_1756823A1DFA7C8F
        ');
        $this->addSql('
            DROP TABLE claro_dropzonebundle_revision
        ');
        $this->addSql('
            DROP TABLE claro_dropzonebundle_revision_comment
        ');
        $this->addSql('
            DROP TABLE claro_dropzonebundle_drop_comment
        ');
        $this->addSql('
            DROP INDEX IDX_E846CAA81DFA7C8F ON claro_dropzonebundle_document
        ');
        $this->addSql('
            ALTER TABLE claro_dropzonebundle_document 
            DROP revision_id
        ');
        $this->addSql('
            ALTER TABLE claro_dropzonebundle_dropzone 
            DROP revision_enabled
        ');
    }
}
