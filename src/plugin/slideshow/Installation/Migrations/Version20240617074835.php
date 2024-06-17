<?php

namespace Claroline\SlideshowBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2024/06/17 07:48:36
 */
final class Version20240617074835 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_slide 
            DROP FOREIGN KEY FK_9F1DBC778B14E343
        ');
        $this->addSql('
            ALTER TABLE claro_slide 
            ADD CONSTRAINT FK_F8EC39708B14E343 FOREIGN KEY (slideshow_id) 
            REFERENCES claro_slideshow (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_slide 
            DROP FOREIGN KEY FK_F8EC39708B14E343
        ');
        $this->addSql('
            ALTER TABLE claro_slide 
            ADD CONSTRAINT FK_9F1DBC778B14E343 FOREIGN KEY (slideshow_id) 
            REFERENCES claro_slideshow (id)
        ');
    }
}
