<?php
/**
 * Created by PhpStorm.
 * User: panos
 * Date: 9/23/14
 * Time: 2:26 PM.
 */

namespace Icap\WebsiteBundle\Entity;

use Claroline\CoreBundle\Library\Utilities\Enum;

class WebsitePageTypeEnum extends Enum
{
    const ROOT_PAGE = 'root';
    const BLANK_PAGE = 'blank';
    const URL_PAGE = 'url';
    const RESOURCE_PAGE = 'resource';
}
