<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\TemplateBundle\Controller;

use Claroline\AppBundle\API\Finder\FinderRequest;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\TemplateBundle\Entity\Template;
use Symfony\Component\HttpFoundation\StreamedJsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/template', name: 'apiv2_template_')]
class TemplateController extends AbstractCrudController
{
    public static function getName(): string
    {
        return 'template';
    }

    public static function getClass(): string
    {
        return Template::class;
    }

    #[Route(path: '/{type}', name: 'type_list', methods: ['GET'])]
    public function listByTypeAction(
        #[MapQueryString]
        ?FinderRequest $finderRequest = new FinderRequest(),
        ?string $type = null
    ): StreamedJsonResponse {
        if ($type) {
            $finderRequest->addFilter('type', $type);
        }

        return parent::listAction($finderRequest);
    }
}
