<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HeVinci\UrlBundle\Controller;

use Claroline\CoreBundle\Manager\Template\PlaceholderManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/url')]
class UrlController
{
    public function __construct(
        private readonly PlaceholderManager $placeholderManager
    ) {
    }

    #[Route(path: '/placeholders', name: 'apiv2_url_placeholders', methods: ['GET'])]
    public function getPlaceholdersAction(): JsonResponse
    {
        return new JsonResponse(
            $this->placeholderManager->getAvailablePlaceholders()
        );
    }
}
