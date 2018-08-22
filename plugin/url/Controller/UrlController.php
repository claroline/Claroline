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
use HeVinci\UrlBundle\Entity\Url;
use  Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route("/url")
 */
class UrlController extends AbstractCrudController
{
    public function getName()
    {
        return 'url';
    }

    public function getClass()
    {
        return Url::class;
    }
}
