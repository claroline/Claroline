<?php

namespace Innova\CollecticielBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/12/08 11:40:31
 */
class Version20151208114028 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_return_recept_type 
            DROP FOREIGN KEY FK_A32DC238E0C802A4
        ');
        $this->addSql('
            DROP INDEX IDX_A32DC238E0C802A4 ON innova_collecticielbundle_return_recept_type
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_return_recept_type 
            DROP return_receipt_id
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_return_recept 
            DROP FOREIGN KEY FK_3515291BE0C802A4
        ');
        $this->addSql('
            DROP INDEX IDX_3515291BE0C802A4 ON innova_collecticielbundle_return_recept
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_return_recept 
            ADD return_receipt_type_id INT DEFAULT NULL, 
            DROP return_receipt_id
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_return_recept 
            ADD CONSTRAINT FK_3515291BFD252543 FOREIGN KEY (return_receipt_type_id) 
            REFERENCES innova_collecticielbundle_return_recept_type (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE INDEX IDX_3515291BFD252543 ON innova_collecticielbundle_return_recept (return_receipt_type_id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_return_recept 
            DROP FOREIGN KEY FK_3515291BFD252543
        ');
        $this->addSql('
            DROP INDEX IDX_3515291BFD252543 ON innova_collecticielbundle_return_recept
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_return_recept 
            ADD return_receipt_id INT NOT NULL, 
            DROP return_receipt_type_id
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_return_recept 
            ADD CONSTRAINT FK_3515291BE0C802A4 FOREIGN KEY (return_receipt_id) 
            REFERENCES innova_collecticielbundle_return_recept_type (id)
        ');
        $this->addSql('
            CREATE INDEX IDX_3515291BE0C802A4 ON innova_collecticielbundle_return_recept (return_receipt_id)
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_return_recept_type 
            ADD return_receipt_id INT NOT NULL
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_return_recept_type 
            ADD CONSTRAINT FK_A32DC238E0C802A4 FOREIGN KEY (return_receipt_id) 
            REFERENCES innova_collecticielbundle_return_recept (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE INDEX IDX_A32DC238E0C802A4 ON innova_collecticielbundle_return_recept_type (return_receipt_id)
        ');
    }
}
