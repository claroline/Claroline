<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 1/17/17
 */

namespace Claroline\CoreBundle\Controller\Administration;

use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("canOpenAdminTool('main_settings')")
 */
class ArchiveController extends Controller
{
    /**
     * ArchiveController constructor.
     *
     * @DI\InjectParams({
     *     "archivePath" = @DI\Inject("%claroline.param.archive_directory%")
     * })
     */
    public function __construct($archivePath)
    {
        $this->archivePath = $archivePath;
    }

    /**
     * @EXT\Route("/download/{archive}", name="claro_admin_archive_download")
     * @EXT\Template
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function downloadAction($archive)
    {
        $file = $this->archivePath.DIRECTORY_SEPARATOR.$archive;

        $response = new BinaryFileResponse($file, 200, ['Content-Disposition' => "attachment; filename={$file}"]);

        return $response;
    }
}
