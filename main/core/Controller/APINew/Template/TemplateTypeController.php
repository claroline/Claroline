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
use Claroline\CoreBundle\Entity\Template\TemplateType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;

/**
 * @EXT\Route("/template_type")
 */
class TemplateTypeController extends AbstractCrudController
{
    public function getName()
    {
        return 'template_type';
    }

    public function getClass()
    {
        return TemplateType::class;
    }

    public function getIgnore()
    {
        return ['exist', 'copyBulk', 'schema', 'doc', 'find', 'create', 'update', 'deleteBulk'];
    }
}
