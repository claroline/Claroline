<?php

namespace Claroline\OpenBadgeBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231214110500 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            UPDATE `claro_template_content`
            LEFT JOIN `claro_template` ct on ct.id = `claro_template_content`.`template_id`
            LEFT JOIN `claro_template_type` ctt on ctt.id = ct.`claro_template_type`
            SET `title` = REPLACE(`title`, \'%issued_on%\', \'%issued_on_datetime%\')
            WHERE `title` LIKE \'%issued_on%\' AND ctt.entity_name LIKE \'%badge_%\'
        ');

        $this->addSql('
            UPDATE `claro_template_content`
            LEFT JOIN `claro_template` ct on ct.id = `claro_template_content`.`template_id`
            LEFT JOIN `claro_template_type` ctt on ctt.id = ct.`claro_template_type`
            SET `content` = REPLACE(`content`, \'%issued_on%\', \'%issued_on_datetime%\')
            WHERE `content` LIKE \'%issued_on%\' AND ctt.entity_name LIKE \'%badge_%\'
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            UPDATE `claro_template_content`
            LEFT JOIN `claro_template` ct on ct.id = `claro_template_content`.`template_id`
            LEFT JOIN `claro_template_type` ctt on ctt.id = ct.`claro_template_type`
            SET `content` = REPLACE(`content`, \'%issued_on_datetime%\', \'%issued_on%\')
            WHERE `title` LIKE \'%issued_on_datetime%\' AND ctt.entity_name LIKE \'%badge_%\'
        ');

        $this->addSql('
            UPDATE `claro_template_content`
            LEFT JOIN `claro_template` ct on ct.id = `claro_template_content`.`template_id`
            LEFT JOIN `claro_template_type` ctt on ctt.id = ct.`claro_template_type`
            SET `content` = REPLACE(`content`, \'%issued_on_datetime%\', \'%issued_on%\')
            WHERE `content` LIKE \'%issued_on_datetime%\' AND ctt.entity_name LIKE \'%badge_%\'
        ');
    }
}
