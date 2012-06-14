<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * DirectoryManager will redirect to this controller once a directory is "open".
 */
class DirectoryController extends Controller
{

    /**
     * This method is called when the resource controller openAction is fired for
     * a directory. This is staying as an exemple and a test.
     * If it's removed, only "defaultClickAction" for a directory should remain.
     *
     * @param integer $id
     *
     * @return Response
     */
    public function indexAction($id)
    {
        return new Response("index");
    }

}