<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nicolas
 * Date: 07/11/13
 * Time: 14:29
 * To change this template use File | Settings | File Templates.
 */

namespace Icap\LessonBundle\Installation\Updater;

use Claroline\InstallationBundle\Updater\Updater;
use Icap\LessonBundle\Entity\Chapter;
use Icap\LessonBundle\Entity\Lesson;

class Updater13 extends Updater
{
    private $container;
    private $conn;

    public function __construct($container)
    {
        $this->container = $container;
        $this->conn = $container->get('doctrine.dbal.default_connection');
    }

    public function postUpdate()
    {
        //generating missing titles (root chapters)
        $this->setChapterTitles();
        //generate missing slugs
        $this->setSlug();
    }

    public function setChapterTitles()
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $chapters = $em->getRepository('IcapLessonBundle:Chapter')->findAll();
        foreach ($chapters as $chapter) {
            if ($chapter->getTitle() == null) {
                //if root chapter, take name of its lesson
                if ($chapter->getRoot() == $chapter->getId()) {
                    $chapter->setTitle('root_'.$chapter->getId());
                } else {
                    //case treated to match current database state (title nullable), tho this case shouldnt happen since UI prevent inputing empty titles
                    $chapter->setTitle('Default title');
                }
            }
        }
        $em->flush();
    }

    public function setSlug()
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $chapters = $em->getRepository('IcapLessonBundle:Chapter')->findAll();
        $cpt = 0;
        //first pass needed to set slug value to something other than NULL, otherwise entities wont be persisted and wont trigger slug generation
        foreach ($chapters as $chapter) {
            if ($chapter->getSlug() == null) {
                $chapter->setSlug('slug_placeholder_'.$cpt++);
            }
        }
        $em->flush();
        //setting slug to null value will regenerate it when em is flushed
        foreach ($chapters as $chapter) {
            $chapter->setSlug(null);
        }
        $em->flush();
    }
}
