<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AuthenticationBundle\Controller\API\Oauth;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route("/oauth")
 */
class OauthController extends AbstractCrudController
{
    public function getClass()
    {
        return 'Claroline\AuthenticationBundle\Entity\Oauth\OauthUser';
    }

    public function getName()
    {
        return 'oauth';
    }
}
