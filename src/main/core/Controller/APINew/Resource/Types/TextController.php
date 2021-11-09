<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\APINew\Resource\Types;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\Resource\Text;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Claroline\CoreBundle\Manager\Template\PlaceholderManager;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Dompdf\Dompdf;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("resource_text")
 */
class TextController extends AbstractCrudController
{
    use PermissionCheckerTrait;

    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var PlaceholderManager */
    private $placeholderManager;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        PlaceholderManager $placeholderManager
    ) {
        $this->authorization = $authorization;
        $this->placeholderManager = $placeholderManager;
    }

    public function getClass()
    {
        return Text::class;
    }

    public function getIgnore()
    {
        return ['create', 'exist', 'list', 'copyBulk', 'deleteBulk', 'schema', 'find', 'get'];
    }

    public function getName()
    {
        return 'resource_text';
    }

    /**
     * @Route("/{id}/pdf", name="apiv2_resource_text_download_pdf", methods={"GET"})
     * @EXT\ParamConverter("text", class="Claroline\CoreBundle\Entity\Resource\Text", options={"mapping": {"id": "id"}})
     */
    public function downloadPdfAction(Text $text): StreamedResponse
    {
        $this->checkPermission('EXPORT', $text->getResourceNode(), [], true);

        $domPdf = new Dompdf([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
        ]);
        $domPdf->loadHtml(
            $this->placeholderManager->replacePlaceholders($text->getContent() ?? '')
        );

        // Render the HTML as PDF
        $domPdf->render();

        return new StreamedResponse(function () use ($domPdf) {
            echo $domPdf->output();
        }, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename='.TextNormalizer::toKey($text->getName()).'.pdf',
        ]);
    }
}
