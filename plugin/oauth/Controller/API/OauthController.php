<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icap\OAuthBundle\Controller\API;

use Claroline\AppBundle\Annotations\ApiMeta;
use Claroline\AppBundle\Controller\AbstractCrudController;
use FOS\RestBundle\Controller\Annotations\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @ApiMeta(class="Icap\OAuthBundle\Entity\OauthUser")
 * @Route("/oauth")
 */
class OauthController extends AbstractCrudController
{
    public function getName()
    {
        return 'oauth';
    }
}
