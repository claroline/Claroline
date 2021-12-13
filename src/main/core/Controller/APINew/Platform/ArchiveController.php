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

namespace Claroline\CoreBundle\Controller\APINew\Platform;

use Claroline\AppBundle\Controller\AbstractSecurityController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Routing\Annotation\Route;

class ArchiveController extends AbstractSecurityController
{
    /** @var string */
    private $archivePath;

    public function __construct(string $archivePath)
    {
        $this->archivePath = $archivePath;
    }

    /**
     * @Route("/download/{archive}", name="claro_admin_archive_download")
     */
    public function downloadAction($archive): BinaryFileResponse
    {
        $this->canOpenAdminTool('main_settings');

        $file = $this->archivePath.DIRECTORY_SEPARATOR.$archive;

        return new BinaryFileResponse($file, 200, [
            'Content-Disposition' => "attachment; filename={$file}",
        ]);
    }
}
