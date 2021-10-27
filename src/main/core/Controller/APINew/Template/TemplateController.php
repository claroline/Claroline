<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\APINew\Template;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\Template\Template;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/template")
 */
class TemplateController extends AbstractCrudController
{
    public function getName()
    {
        return 'template';
    }

    public function getClass()
    {
        return Template::class;
    }
}
