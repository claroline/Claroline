<?php

namespace Icap\DropzoneBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/03/19 10:27:40
 */
class Version20140319102737 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_correction 
            ADD COLUMN correctionDenied BOOLEAN NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_correction 
            ADD COLUMN correctionDeniedComment CLOB DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_CDA81F40A76ED395
        ");
        $this->addSql("
            DROP INDEX IDX_CDA81F404D224760
        ");
        $this->addSql("
            DROP INDEX IDX_CDA81F40A8C6E7BD
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__icap__dropzonebundle_correction AS 
            SELECT id, 
            user_id, 
            drop_id, 
            drop_zone_id, 
            total_grade, 
            comment, 
            valid, 
            start_date, 
            last_open_date, 
            end_date, 
            finished, 
            editable, 
            reporter, 
            reportComment 
            FROM icap__dropzonebundle_correction
        ");
        $this->addSql("
            DROP TABLE icap__dropzonebundle_correction
        ");
        $this->addSql("
            CREATE TABLE icap__dropzonebundle_correction (
                id INTEGER NOT NULL, 
                user_id INTEGER NOT NULL, 
                drop_id INTEGER DEFAULT NULL, 
                drop_zone_id INTEGER NOT NULL, 
                total_grade NUMERIC(10, 2) DEFAULT NULL, 
                comment CLOB DEFAULT NULL, 
                valid BOOLEAN NOT NULL, 
                start_date DATETIME NOT NULL, 
                last_open_date DATETIME NOT NULL, 
                end_date DATETIME DEFAULT NULL, 
                finished BOOLEAN NOT NULL, 
                editable BOOLEAN NOT NULL, 
                reporter BOOLEAN NOT NULL, 
                reportComment CLOB DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_CDA81F40A76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_CDA81F404D224760 FOREIGN KEY (drop_id) 
                REFERENCES icap__dropzonebundle_drop (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_CDA81F40A8C6E7BD FOREIGN KEY (drop_zone_id) 
                REFERENCES icap__dropzonebundle_dropzone (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO icap__dropzonebundle_correction (
                id, user_id, drop_id, drop_zone_id, 
                total_grade, comment, valid, start_date, 
                last_open_date, end_date, finished, 
                editable, reporter, reportComment
            ) 
            SELECT id, 
            user_id, 
            drop_id, 
            drop_zone_id, 
            total_grade, 
            comment, 
            valid, 
            start_date, 
            last_open_date, 
            end_date, 
            finished, 
            editable, 
            reporter, 
            reportComment 
            FROM __temp__icap__dropzonebundle_correction
        ");
        $this->addSql("
            DROP TABLE __temp__icap__dropzonebundle_correction
        ");
        $this->addSql("
            CREATE INDEX IDX_CDA81F40A76ED395 ON icap__dropzonebundle_correction (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_CDA81F404D224760 ON icap__dropzonebundle_correction (drop_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_CDA81F40A8C6E7BD ON icap__dropzonebundle_correction (drop_zone_id)
        ");
    }
}