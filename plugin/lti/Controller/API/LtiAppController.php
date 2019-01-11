<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UJM\LtiBundle\Controller\API;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use UJM\LtiBundle\Entity\LtiApp;

/**
 * @EXT\Route("/lti")
 */
class LtiAppController extends AbstractCrudController
{
    public function getName()
    {
        return 'lti';
    }

    public function getClass()
    {
        return LtiApp::class;
    }

    public function getIgnore()
    {
        return ['exist', 'copyBulk', 'schema', 'find'];
    }
}
