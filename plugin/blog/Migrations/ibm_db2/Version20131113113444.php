<?php

namespace Icap\BlogBundle\Migrations\ibm_db2;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2013/11/13 11:34:46
 */
class Version20131113113444 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__blog_options ALTER display_title display_title SMALLINT NOT NULL ALTER banner_activate banner_activate SMALLINT NOT NULL ALTER banner_background_color banner_background_color VARCHAR(255) NOT NULL ALTER banner_height banner_height SMALLINT NOT NULL ALTER banner_background_image_position banner_background_image_position VARCHAR(255) NOT NULL ALTER banner_background_image_repeat banner_background_image_repeat VARCHAR(255) NOT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__blog_options ALTER display_title display_title SMALLINT NOT NULL ALTER banner_activate banner_activate SMALLINT NOT NULL ALTER banner_background_color banner_background_color VARCHAR(255) NOT NULL ALTER banner_height banner_height SMALLINT NOT NULL ALTER banner_background_image_position banner_background_image_position VARCHAR(255) NOT NULL ALTER banner_background_image_repeat banner_background_image_repeat VARCHAR(255) NOT NULL
        ');
    }
}
