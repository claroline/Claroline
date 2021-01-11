<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\APINew;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/logger")
 */
class LoggerController
{
    public function __construct($logDir)
    {
        $this->logDir = $logDir;
    }

    /**
     * @Route("/{subdir}/{name}", name="apiv2_logger_get", methods={"GET"})
     *
     * @todo update import log
     *
     * @return Response
     */
    public function getAction($subdir, $name)
    {
        $file = $this->logDir.DIRECTORY_SEPARATOR.$subdir.DIRECTORY_SEPARATOR.$name.'.json';

        if (file_exists($file)) {
            return new Response(file_get_contents($file));
        }

        return new JsonResponse(['log' => 'no file found']);
    }
}
