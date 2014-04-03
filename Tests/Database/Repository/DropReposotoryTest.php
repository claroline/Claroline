<?php
/**
 * Created by PhpStorm.
 * User: aurelien
 * Date: 03/04/14
 * Time: 11:20
 */

namespace Icap\DropzoneBundle\Repository;

use Claroline\CoreBundle\Library\Testing\RepositoryTestCase;

class DropReposotoryTest extends RepositoryTestCase {

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$repo = self::getRepository('IcapDropzoneBundle:Drop');
    }
} 