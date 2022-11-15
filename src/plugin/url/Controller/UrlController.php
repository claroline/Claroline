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

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Manager\Template\PlaceholderManager;
use HeVinci\UrlBundle\Entity\Url;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/url")
 */
class UrlController extends AbstractCrudController
{
    private $placeholderManager;

    public function __construct(PlaceholderManager $placeholderManager)
    {
        $this->placeholderManager = $placeholderManager;
    }

    public function getName(): string
    {
        return 'url';
    }

    public function getClass(): string
    {
        return Url::class;
    }

    /**
     * @Route("/placeholders", name="apiv2_url_placeholders", methods={"GET"})
     */
    public function getPlaceholdersAction(): JsonResponse
    {
        return new JsonResponse(
            $this->placeholderManager->getAvailablePlaceholders()
        );
    }
}
