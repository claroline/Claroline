<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\Log;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;

class AdministrationController extends Controller
{
    /**
     * @EXT\Route(
     *     "/logs/",
     *     name="claro_admin_logs_show",
     *     defaults={"page" = 1}
     * )
     * @EXT\Route(
     *     "/logs/{page}",
     *     name="claro_admin_logs_show_paginated",
     *     requirements={"page" = "\d+"},
     *     defaults={"page" = 1}
     * )
     *
     * @EXT\Template()
     *
     * Displays logs list using filter parameteres and page number
     *
     * @param $page int The requested page number.
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function logListAction($page)
    {
        return $this->get('claroline.log.manager')->getAdminList($page);
    }
}
