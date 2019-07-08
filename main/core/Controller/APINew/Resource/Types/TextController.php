<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\APINew\Resource\Types;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\Resource\Text;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;

/**
 * @EXT\Route("resource_text")
 */
class TextController extends AbstractCrudController
{
    public function getClass()
    {
        return Text::class;
    }

    public function getIgnore()
    {
        return ['create', 'exist', 'list', 'copyBulk', 'deleteBulk', 'schema', 'find', 'get'];
    }

    public function getName()
    {
        return 'resource_text';
    }
}
