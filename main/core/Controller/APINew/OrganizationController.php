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

use Claroline\CoreBundle\Annotations\ApiMeta;
use Claroline\CoreBundle\API\Options;
use Claroline\CoreBundle\Controller\APINew\Model\HasParent;
use FOS\RestBundle\Controller\Annotations\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @ApiMeta(class="Claroline\CoreBundle\Entity\Organization\Organization")
 * @Route("organization")
 */
class OrganizationController extends AbstractController
{
    use HasParent;

    /**
     * @Route("/list/recursive", name="apiv2_organization_list_recursive")
     */
    public function recursiveListAction(Request $request)
    {
        return new JsonResponse($this->finder->search(
            'Claroline\CoreBundle\Entity\Organization\Organization',
            ['filters' => ['parent' => null]],
            [Options::IS_RECURSIVE]
        ));
    }
}
