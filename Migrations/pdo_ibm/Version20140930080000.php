<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace UJM\ExoBundle\Migrations\pdo_ibm;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20140930080000 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            INSERT INTO ujm_type_matching (value, code) VALUES ('To bind', 1);
        ");
        $this->addSql("
            INSERT INTO ujm_type_matching (value, code) VALUES ('To drag', 2);
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DELETE FROM ujm_type_matching WHERE code=1;
        ");
        $this->addSql("
            DELETE FROM ujm_type_matching WHERE code=2;
        ");

    }

}
